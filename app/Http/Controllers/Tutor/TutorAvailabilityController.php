<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TutorAvailabilityController extends Controller
{
    private const DAYS = [
        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
    ];

    public function index(Request $request): View
    {
        $teacher = $this->teacherFor($request);
        $slots = $teacher->availabilitySlots()->orderByRaw("CASE day_of_week WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 ELSE 7 END")
            ->orderBy('start_time')->get();

        $activeOrders = DB::table('orders')
            ->leftJoin('module_packages', 'orders.package_id', '=', 'module_packages.package_id')
            ->where('orders.teacher_id', $teacher->teacher_id)
            ->whereIn('orders.payment_status', ['settled', 'paid', 'pending'])
            ->where(function ($query) {
                $query->whereIn('orders.payment_status', ['settled', 'paid'])
                    ->orWhere('orders.expires_at', '>', now());
            })
            ->whereNotNull('orders.selected_slot_id')
            ->get(['orders.selected_slot_id', 'orders.payment_status', 'module_packages.package_name', 'module_packages.grade_level', 'module_packages.subject_name'])
            ->groupBy('selected_slot_id');

        $scheduleUsage = DB::table('learning_schedules')
            ->leftJoin('module_packages', 'learning_schedules.package_id', '=', 'module_packages.package_id')
            ->where('learning_schedules.teacher_id', $teacher->teacher_id)
            ->whereNotNull('learning_schedules.slot_id')
            ->whereIn('learning_schedules.schedule_status', ['scheduled', 'in_progress'])
            ->where('learning_schedules.end_time', '>', now())
            ->get(['learning_schedules.slot_id', 'module_packages.package_name', 'module_packages.grade_level', 'module_packages.subject_name'])
            ->groupBy('slot_id');

        $slots = $slots->map(function (TeacherAvailabilitySlot $slot) use ($activeOrders, $scheduleUsage) {
            $usage = $activeOrders->get($slot->slot_id, collect())->first();
            $schedule = $scheduleUsage->get($slot->slot_id, collect())->first();
            $slot->usage = $usage ?? $schedule;
            $slot->is_booked = $slot->usage !== null;
            $slot->day_label = self::DAYS[$slot->day_of_week] ?? $slot->day_of_week;
            $slot->start_label = substr((string) $slot->start_time, 0, 5);
            $slot->end_label = substr((string) $slot->end_time, 0, 5);

            return $slot;
        });

        return view('pages.tutor.master-availability', [
            'teacher' => $teacher,
            'slots' => $slots,
            'days' => self::DAYS,
            'activeSlotsCount' => $slots->where('is_active', true)->count(),
            'availableSlotsCount' => $slots->where('is_active', true)->where('is_booked', false)->count(),
            'bookedSlotsCount' => $slots->where('is_active', true)->where('is_booked', true)->count(),
            'selectedDay' => $request->query('day', now()->format('l')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $data = $request->validate([
            'day_of_week' => ['required', Rule::in(array_keys(self::DAYS))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
        ]);
        $start = \Illuminate\Support\Carbon::createFromFormat('!H:i', $data['start_time']);
        $end = filled($data['end_time'] ?? null)
            ? \Illuminate\Support\Carbon::createFromFormat('!H:i', $data['end_time'])
            : $start->copy()->addHour();
        $durationMinutes = (int) (($end->getTimestamp() - $start->getTimestamp()) / 60);
        if ($durationMinutes !== 60) {
            return back()->withInput()->withErrors(['end_time' => 'Durasi setiap slot Master Availability harus 60 menit.']);
        }
        $data['start_time'] = $start->format('H:i:s');
        $data['end_time'] = $end->format('H:i:s');

        $slot = DB::transaction(function () use ($teacher, $data) {
            Teacher::query()->whereKey($teacher->teacher_id)->lockForUpdate()->firstOrFail();
            $overlapExists = $teacher->availabilitySlots()
                ->where('day_of_week', $data['day_of_week'])
                ->where('is_active', true)
                ->where('start_time', '<', $data['end_time'])
                ->where('end_time', '>', $data['start_time'])
                ->exists();

            if ($overlapExists) {
                return null;
            }

            return $teacher->availabilitySlots()->create([
                'day_of_week' => $data['day_of_week'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'learning_method' => null,
                'is_active' => true,
                'is_available' => true,
            ]);
        });

        if (! $slot) {
            return back()->withInput()->withErrors(['start_time' => 'Slot ini berbenturan dengan waktu ketersediaan aktif yang sudah ada.']);
        }

        return redirect()->route('tutor.availability', ['day' => $data['day_of_week']])->with('status', 'Slot ketersediaan berhasil ditambahkan ke Master Availability.');
    }

    public function toggle(Request $request, string $slotId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $slot = $teacher->availabilitySlots()->whereKey($slotId)->firstOrFail();
        abort_if($this->isBooked($teacher, $slot), 409, 'Slot sedang terikat dengan pemesanan atau jadwal kelas.');
        $slot->update(['is_active' => ! $slot->is_active, 'is_available' => ! $slot->is_active]);

        return back()->with('status', 'Status slot ketersediaan berhasil diperbarui.');
    }

    public function destroy(Request $request, string $slotId): RedirectResponse
    {
        $teacher = $this->teacherFor($request);
        $slot = $teacher->availabilitySlots()->whereKey($slotId)->firstOrFail();
        abort_if($this->isBooked($teacher, $slot), 409, 'Slot yang sudah terikat pada kelas tidak dapat dihapus.');
        $slot->delete();

        return back()->with('status', 'Slot ketersediaan berhasil dihapus.');
    }

    private function isBooked(Teacher $teacher, TeacherAvailabilitySlot $slot): bool
    {
        $hasOrder = DB::table('orders')
            ->where('teacher_id', $teacher->teacher_id)
            ->where('selected_slot_id', $slot->slot_id)
            ->where(function ($query) {
                $query->whereIn('payment_status', ['settled', 'paid'])
                    ->orWhere(fn ($pending) => $pending->where('payment_status', 'pending')->where('expires_at', '>', now()));
            })->exists();

        $hasFutureSchedule = DB::table('learning_schedules')
            ->where('teacher_id', $teacher->teacher_id)
            ->where('slot_id', $slot->slot_id)
            ->whereIn('schedule_status', ['scheduled', 'in_progress'])
            ->where('end_time', '>', now())
            ->exists();

        return $hasOrder || $hasFutureSchedule;
    }

    private function teacherFor(Request $request): Teacher
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['teacher', 'tutor'], true), 403);

        return Teacher::query()->with('user')->where('user_id', $user->user_id)->firstOrFail();
    }
}
