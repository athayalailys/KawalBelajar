<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingCheckoutRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\Student;
use App\Models\TeacherAvailabilitySlot;
use App\Services\Payment\XenditInvoiceService;
use App\Services\Student\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly XenditInvoiceService $xenditInvoiceService,
    ) {}

    public function getTutorSlots(string $teacherId, Request $request): JsonResponse
    {
        $filters = Validator::make($request->query(), [
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
            'package_id' => ['nullable', 'uuid', 'exists:module_packages,package_id'],
        ])->validate();

        return response()->json([
            'success' => true,
            'data' => $this->bookingService->getTeacherSlotsWithClashDetection($teacherId, $filters['learning_method'], $filters['package_id'] ?? null),
        ]);
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'teacher_id' => ['required', 'uuid', 'exists:teachers,teacher_id'],
            'package_id' => ['required', 'uuid', 'exists:module_packages,package_id'],
            'learning_method' => ['required', 'string', 'in:home_visit,online_zoom'],
            'session_package' => ['required', 'integer', 'in:1,4'],
            'format' => ['required', 'string', 'in:solo,host,joiner'],
            'target_capacity' => ['required_if:format,host', 'nullable', 'integer', 'min:2', 'max:20'],
            'squad_code' => ['required_if:format,joiner', 'nullable', 'string', 'exists:squads,squad_code'],
        ])->validate();

        $student = $this->studentFor($request);
        $pricing = $this->bookingService->calculatePricing(
            $student,
            $validated['teacher_id'],
            $validated['package_id'],
            $validated['learning_method'],
            (int) $validated['session_package'],
            $validated['format'],
            isset($validated['target_capacity']) ? (int) $validated['target_capacity'] : null,
            $validated['squad_code'] ?? null,
        );

        $squad = $pricing['squad'];
        unset($pricing['squad']);
        if ($squad) {
            $pricing['squad'] = [
                'squad_id' => $squad->squad_id,
                'squad_code' => $squad->squad_code,
                'target_capacity' => $squad->target_capacity,
                'active_members' => $this->bookingService->countActiveMembers($squad),
            ];
        }

        return response()->json(['success' => true, 'data' => $pricing]);
    }

    public function checkout(BookingCheckoutRequest $request): JsonResponse
    {
        $student = $this->studentFor($request);
        $validated = $request->validated();

        if ($validated['learning_method'] === 'home_visit' && blank($student->saved_address)) {
            throw ValidationException::withMessages([
                'learning_method' => 'Tambahkan alamat rumah sebelum memilih metode Home Visit.',
            ]);
        }

        $created = DB::transaction(function () use ($student, $validated) {
            $slot = TeacherAvailabilitySlot::query()
                ->whereKey($validated['selected_slot_id'])
                ->where('teacher_id', $validated['teacher_id'])
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$slot || ($slot->learning_method && $slot->learning_method !== $validated['learning_method'])) {
                throw ValidationException::withMessages(['selected_slot_id' => 'Jadwal tidak tersedia untuk tutor atau metode belajar ini.']);
            }

            $package = \App\Models\ModulePackage::query()->whereKey($validated['package_id'])->firstOrFail();
            if ($package->availabilitySlots()->exists() && ! $package->availabilitySlots()->whereKey($slot->slot_id)->exists()) {
                throw ValidationException::withMessages(['selected_slot_id' => 'Slot ini tidak termasuk jadwal yang dipilih untuk kelas.']);
            }

            $pricing = $this->bookingService->calculatePricing(
                $student,
                $validated['teacher_id'],
                $validated['package_id'],
                $validated['learning_method'],
                (int) $validated['session_package'],
                $validated['format'],
                isset($validated['target_capacity']) ? (int) $validated['target_capacity'] : null,
                $validated['squad_code'] ?? null,
            );

            $squad = $pricing['squad'];
            if ($validated['format'] === 'joiner' && $squad->selected_slot_id !== $slot->slot_id) {
                throw ValidationException::withMessages(['selected_slot_id' => 'Pilih jadwal yang sama dengan jadwal squad.']);
            }

            if ($validated['format'] !== 'joiner') {
                $slotAlreadyReserved = Order::query()
                    ->where('teacher_id', $slot->teacher_id)
                    ->whereHas('selectedSlot', fn ($query) => $query
                        ->where('day_of_week', $slot->day_of_week)
                        ->where('start_time', $slot->start_time))
                    ->where(function ($query) {
                        $query->where('payment_status', 'settled')
                            ->orWhere(fn ($pending) => $pending->where('payment_status', 'pending')->where('expires_at', '>', now()));
                    })
                    ->exists();

                if ($slotAlreadyReserved) {
                    throw ValidationException::withMessages(['selected_slot_id' => 'Jadwal baru saja dipesan. Pilih jadwal lain.']);
                }
            }

            if ($validated['format'] === 'host') {
                $squad = Squad::create([
                    'squad_code' => 'SQUAD-' . strtoupper(Str::random(10)),
                    'host_student_id' => $student->student_id,
                    'teacher_id' => $validated['teacher_id'],
                    'package_id' => $validated['package_id'],
                    'selected_slot_id' => $slot->slot_id,
                    'learning_method' => $validated['learning_method'],
                    'target_capacity' => (int) $validated['target_capacity'],
                    'discount_rate' => $pricing['discount_percentage'],
                    'status' => 'open',
                ]);
            }

            $order = Order::create([
                'student_id' => $student->student_id,
                'teacher_id' => $validated['teacher_id'],
                'package_id' => $validated['package_id'],
                'squad_id' => $squad?->squad_id,
                'learning_method' => $validated['learning_method'],
                'session_package' => (int) $validated['session_package'],
                'selected_slot_id' => $slot->slot_id,
                'total_amount' => $pricing['total_amount'],
                'payment_status' => 'pending',
                'invoice_number' => 'INV-KB-' . strtoupper((string) Str::uuid()),
                'expires_at' => now()->addDay(),
                'order_date' => now(),
                'order_status' => 'pending',
            ]);

            if ($squad) {
                SquadMember::create([
                    'squad_id' => $squad->squad_id,
                    'student_id' => $student->student_id,
                    'order_id' => $order->order_id,
                ]);

                if ($this->bookingService->countActiveMembers($squad) >= $squad->target_capacity) {
                    $squad->update(['status' => 'full']);
                }
            }

            return compact('order', 'squad');
        });

        try {
            $invoice = $this->xenditInvoiceService->createInvoice($created['order'], $request->user(), $validated['payment_method'] ?? null);
            $created['order']->update([
                'xendit_invoice_id' => $invoice['invoice_id'],
                'xendit_payment_url' => $invoice['payment_url'],
            ]);
            if (Schema::hasTable('payments')) {
                Payment::updateOrCreate(
                    ['order_id' => $created['order']->order_id],
                    [
                        'xendit_invoice_id' => $invoice['invoice_id'],
                        'payment_method' => 'Xendit',
                        'amount' => $created['order']->total_amount,
                        'payment_status' => 'pending',
                    ],
                );
            }
        } catch (Throwable $exception) {
            $created['order']->update(['payment_status' => 'failed', 'order_status' => 'failed']);
            if ($validated['format'] === 'host') {
                $created['squad']?->update(['status' => 'cancelled']);
            }
            Log::error('Xendit invoice creation failed.', ['order_id' => $created['order']->order_id]);

            return response()->json([
                'success' => false,
                'message' => 'Invoice pembayaran belum dapat dibuat. Silakan coba kembali.',
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat. Silakan lanjutkan pembayaran.',
            'data' => [
                'order_id' => $created['order']->order_id,
                'invoice_number' => $created['order']->invoice_number,
                'total_amount' => $created['order']->total_amount,
                'payment_status' => $created['order']->payment_status,
                'payment_url' => $created['order']->xendit_payment_url,
                'expires_at' => $created['order']->expires_at?->toIso8601String(),
                'squad_code' => $created['squad']?->squad_code,
            ],
        ], 201);
    }

    private function studentFor(Request $request): Student
    {
        $student = $request->user()?->student;
        abort_unless($student instanceof Student, 403, 'Profil pembelajar tidak ditemukan.');

        return $student;
    }

    public function index(Request $request)
    {
        $student = $request->user()?->student;

        return view('pages.student.booking-wizard', [
            'student' => $student,
            'educationLevel' => $student?->education_level,
            'studentAddress' => $student?->saved_address ?? $student?->address,
        ]);
    }

    public function paymentStatus(Request $request, string $orderId): JsonResponse
    {
        $student = $this->studentFor($request);
        $order = Order::query()
            ->where('order_id', $orderId)
            ->where('student_id', $student->student_id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->order_id,
                'payment_status' => $order->payment_status,
                'total_amount' => (float) $order->total_amount,
            ],
        ]);
    }
}
