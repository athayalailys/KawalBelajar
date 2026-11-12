<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use App\Models\User;

class ZoomGatingService
{
    public function canAccessMeetingUrl(LearningSession $session, User $user): bool
    {
        $session->loadMissing('order');
        $role = strtolower((string) $user->role);

        if (in_array($role, ['teacher', 'tutor'], true)) {
            return $user->teacher?->teacher_id !== null
                && $user->teacher->teacher_id === $session->teacher_id;
        }

        if ($role !== 'student') {
            return false;
        }

        if (!$session->hasStarted()) {
            return false;
        }

        $studentId = $user->student?->student_id;
        if (!$studentId || ($session->student_id !== $studentId && $session->order?->student_id !== $studentId)) {
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
        $session->loadMissing('schedule');
        $hasStarted = $session->hasStarted();
        $hasCompletedPreTest = $this->isPreTestPassed($session->session_id, $studentId);

        if (!$hasStarted || !$hasCompletedPreTest) {
            return [
                'is_unlocked' => false,
                'message' => !$hasStarted
                    ? 'Ruang belajar tersedia setelah waktu sesi dimulai.'
                    : 'Wajib selesaikan Pre-Test terlebih dahulu untuk membuka ruang belajar.',
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
        $session->loadMissing('schedule');
        $hasStarted = $session->hasStarted();
        $evaluation = EvaluationScore::query()
            ->where('session_id', $session->session_id)
            ->where('student_id', $studentId)
            ->first(['pretest_score', 'posttest_score']);
        $hasPreTest = $evaluation?->pretest_score !== null;
        $postTestSubmitted = $evaluation?->posttest_score !== null;
        $isLocked = !$hasStarted || !$hasPreTest;

        return [
            'is_locked' => $isLocked,
            'session_status' => $session->session_status ?? $session->status,
            'can_start_exam' => $hasStarted && !$hasPreTest,
            'can_take_post_test' => $hasStarted && $hasPreTest && !$postTestSubmitted,
            'gate_reason' => !$hasStarted ? 'not_started' : (!$hasPreTest ? 'pretest_required' : null),
            'pretest_score' => $evaluation?->pretest_score,
            'posttest_score' => $evaluation?->posttest_score,
            'zoom_meeting_url' => $isLocked ? null : $session->zoom_meeting_url,
            'address' => $isLocked ? null : ($session->onsite_address ?? $session->student_address),
            'landmark' => $isLocked ? null : $session->address_landmark,
        ];
    }
}
