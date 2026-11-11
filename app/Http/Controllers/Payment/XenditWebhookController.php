<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\LearningMaterial;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Student\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class XenditWebhookController extends Controller
{
    public function handleCallback(Request $request, BookingService $bookingService): JsonResponse
    {
        $expectedToken = (string) config('services.xendit.webhook_token');
        $receivedToken = (string) $request->header('x-callback-token');

        if ($expectedToken === '' || $receivedToken === '' || !hash_equals($expectedToken, $receivedToken)) {
            return response()->json(['message' => 'Invalid webhook token.'], 403);
        }

        $payload = Validator::make($request->all(), [
            'id' => ['required', 'string', 'max:255'],
            'external_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:PAID,SETTLED,EXPIRED,FAILED'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ])->validate();

        $order = DB::transaction(function () use ($payload, $bookingService) {
            $order = Order::query()
                ->where('xendit_invoice_id', $payload['id'])
                ->where('invoice_number', $payload['external_id'])
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return null;
            }

            if (isset($payload['amount']) && (int) round((float) $payload['amount'] * 100) !== (int) round((float) $order->total_amount * 100)) {
                return false;
            }

            if ($order->payment_status === 'settled') {
                return $order;
            }

            $status = match ($payload['status']) {
                'PAID', 'SETTLED' => 'settled',
                'EXPIRED' => 'expired',
                'FAILED' => 'failed',
            };

            $order->update([
                'payment_status' => $status,
                'order_status' => match ($status) {
                    'settled' => 'confirmed',
                    'expired' => 'expired',
                    'failed' => 'failed',
                },
            ]);

            if ($status === 'settled') {
                $this->createScheduledSessions($order);
            }

            if (Schema::hasTable('payments')) {
                Payment::query()->where('order_id', $order->order_id)->update([
                    'payment_status' => $status,
                    'paid_at' => $status === 'settled' ? now() : null,
                ]);
            }

            if ($status === 'expired' && $order->squad_id) {
                $squad = $order->squad()->lockForUpdate()->first();
                if ($squad && $squad->status === 'full' && $bookingService->countActiveMembers($squad) < $squad->target_capacity) {
                    $squad->update(['status' => 'open']);
                }
            }

            return $order;
        });

        if ($order === null) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order === false) {
            return response()->json(['message' => 'Invoice amount does not match the order.'], 422);
        }

        return response()->json(['success' => true]);
    }

    private function createScheduledSessions(Order $order): void
    {
        if ($order->learningSessions()->exists()) {
            return;
        }

        $slot = $order->selectedSlot()->lockForUpdate()->first();
        if (!$slot || $slot->teacher_id !== $order->teacher_id) {
            throw new \RuntimeException('The reserved tutor slot is unavailable.');
        }

        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $weekday = collect($weekdays)->first(fn (string $day) => strcasecmp($day, $slot->day_of_week) === 0);
        if (!$weekday) {
            throw new \RuntimeException('The reserved tutor slot has an invalid weekday.');
        }

        $firstStart = Carbon::now(config('app.timezone'))->next($weekday)->setTimeFromTimeString($slot->start_time);
        $firstEnd = $firstStart->copy()->setTimeFromTimeString($slot->end_time);
        if ($firstEnd->lessThanOrEqualTo($firstStart)) {
            $firstEnd->addDay();
        }

        $student = $order->student()->first();
        $address = $order->learning_method === 'home_visit' ? $student?->saved_address : null;
        for ($sessionIndex = 0; $sessionIndex < (int) $order->session_package; $sessionIndex++) {
            $start = $firstStart->copy()->addWeeks($sessionIndex);
            $end = $firstEnd->copy()->addWeeks($sessionIndex);
            $schedule = LearningSchedule::create([
                'teacher_id' => $order->teacher_id,
                'package_id' => $order->package_id,
                'method' => $order->learning_method,
                'slot_id' => $slot->slot_id,
                'location' => $address,
                'start_time' => $start,
                'end_time' => $end,
            ]);

            LearningSession::create([
                'order_id' => $order->order_id,
                'student_id' => $order->student_id,
                'teacher_id' => $order->teacher_id,
                'schedule_id' => $schedule->schedule_id,
                'session_number' => $sessionIndex + 1,
                'material_id' => LearningMaterial::query()
                    ->where('package_id', $order->package_id)
                    ->where('session_number', $sessionIndex + 1)
                    ->orderByDesc('created_at')
                    ->value('material_id'),
                'scheduled_at' => $start,
                'duration_minutes' => $start->diffInMinutes($end),
                'learning_method' => $order->learning_method,
                'session_status' => 'scheduled',
                'status' => 'scheduled',
                'onsite_address' => $address,
                'student_address' => $address,
                'latitude' => $order->learning_method === 'home_visit' ? $student?->latitude : null,
                'longitude' => $order->learning_method === 'home_visit' ? $student?->longitude : null,
                'address_landmark' => $order->learning_method === 'home_visit' ? $student?->landmark : null,
            ]);
        }
    }
}
