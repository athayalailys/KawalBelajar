<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use App\Models\TeacherWorkLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function clockIn(string $sessionId, string $teacherId, ?float $latitude = null, ?float $longitude = null): LearningSession
    {
        return DB::transaction(function () use ($sessionId, $teacherId, $latitude, $longitude) {
            $session = LearningSession::with('order')->where('session_id', $sessionId)
                ->where('teacher_id', $teacherId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->absensi_masuk_guru !== null || $session->teacher_clock_in !== null) {
                throw new \DomainException('Tutor sudah melakukan Clock-In pada sesi ini.');
            }

            $now = now();
            $session->update([
                'absensi_masuk_guru' => $now,
                'teacher_clock_in' => $now,
                'status' => 'in_progress',
                'session_status' => 'in_progress',
                'teacher_clock_in_latitude' => $latitude,
                'teacher_clock_in_longitude' => $longitude,
            ]);

            return $session;
        });
    }

    public function clockOut(string $sessionId, string $teacherId, ?float $latitude = null, ?float $longitude = null): LearningSession
    {
        return DB::transaction(function () use ($sessionId, $teacherId, $latitude, $longitude) {
            $session = LearningSession::with('order')->where('session_id', $sessionId)
                ->where('teacher_id', $teacherId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->absensi_masuk_guru === null && $session->teacher_clock_in === null) {
                throw new \DomainException('Tutor belum melakukan Clock-In.');
            }

            if ($session->absensi_keluar_guru !== null || $session->teacher_clock_out !== null) {
                throw new \DomainException('Tutor sudah melakukan Clock-Out pada sesi ini.');
            }

            $now = now();
            $session->update([
                'absensi_keluar_guru' => $now,
                'teacher_clock_out' => $now,
                'status' => 'completed',
                'session_status' => 'completed',
                'teacher_clock_out_latitude' => $latitude,
                'teacher_clock_out_longitude' => $longitude,
            ]);

            $clockInAt = $session->teacher_clock_in ?? $session->absensi_masuk_guru;
            $elapsedMinutes = $clockInAt
                ? max(0, (int) floor(($now->getTimestamp() - Carbon::parse($clockInAt)->getTimestamp()) / 60))
                : 0;
            $durationMinutes = $elapsedMinutes > 0
                ? $elapsedMinutes
                : max(0, (int) ($session->duration_minutes ?? 0));
            $grossAmount = (float) ($session->order?->total_amount ?? $session->order?->personal_amount ?? 0);

            TeacherWorkLog::updateOrCreate(
                ['session_id' => $session->session_id],
                [
                    'teacher_id' => $session->teacher_id,
                    'duration_minutes' => $durationMinutes,
                    'gross_session_amount' => $grossAmount,
                    'teacher_earnings' => round($grossAmount * 0.25, 2),
                    'payroll_status' => 'pending',
                    'created_at' => $now,
                ],
            );

            return $session;
        });
    }
}
