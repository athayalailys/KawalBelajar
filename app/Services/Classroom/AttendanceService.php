<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function clockIn(string $sessionId, string $teacherId, ?float $latitude = null, ?float $longitude = null): LearningSession
    {
        return DB::transaction(function () use ($sessionId, $teacherId) {
            $session = LearningSession::where('session_id', $sessionId)
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
            ]);

            return $session;
        });
    }

    public function clockOut(string $sessionId, string $teacherId): LearningSession
    {
        return DB::transaction(function () use ($sessionId, $teacherId) {
            $session = LearningSession::where('session_id', $sessionId)
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
            ]);

            return $session;
        });
    }
}
