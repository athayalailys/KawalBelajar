<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use App\Models\User;

class ZoomGatingService
{
    public function canAccessMeetingUrl(LearningSession $session, User $user): bool
    {
        $role = strtolower((string) $user->role);

        if (in_array($role, ['teacher', 'tutor'], true)) {
            return $user->teacher?->teacher_id !== null
                && $user->teacher->teacher_id === $session->teacher_id;
        }

        if ($role !== 'student') {
            return false;
        }

        $studentId = $user->student?->student_id;
        if (!$studentId) {
            return false;
        }

        return $this->isPreTestPassed($session->session_id, $studentId);
    }

    /**
     * Memeriksa apakah siswa sudah menyelesaikan Pre-Test untuk sesi ini
     */
    public function isPreTestPassed(string $sessionId, string $studentId): bool
    {
        return EvaluationScore::query()
            ->where('session_id', $sessionId)
            ->where('student_id', $studentId)
            ->whereNotNull('pretest_score')
            ->exists();
    }

    /**
     * Mengembalikan data akses ruang belajar dengan validasi gating
     */
    public function getGatedRoomAccess(LearningSession $session, string $studentId): array
    {
        $hasCompletedPreTest = $this->isPreTestPassed($session->session_id, $studentId);

        if (!$hasCompletedPreTest) {
            return [
                'is_unlocked' => false,
                'message' => 'Wajib selesaikan Pre-Test terlebih dahulu untuk membuka ruang belajar.',
                'zoom_meeting_url' => null,
                'address' => null,
            ];
        }

        return [
            'is_unlocked' => true,
            'message' => 'Akses ruang belajar terbuka.',
            'zoom_meeting_url' => $session->zoom_meeting_url,
            'address' => $session->onsite_address ?? $session->student_address,
            'landmark' => $session->address_landmark,
        ];
    }

    public function checkSessionAccess(LearningSession $session, string $studentId): array
    {
        $isLocked = !$this->isPreTestPassed($session->session_id, $studentId);

        return [
            'is_locked' => $isLocked,
            'zoom_meeting_url' => $isLocked ? null : $session->zoom_meeting_url,
            'address' => $isLocked ? null : ($session->onsite_address ?? $session->student_address),
            'landmark' => $isLocked ? null : $session->address_landmark,
        ];
    }
}
