<?php

namespace App\Services\Student;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use App\Models\Order;
use App\Models\StudentAttendance;
use Illuminate\Support\Carbon;

class StudentDashboardService
{
    public function getDashboardData(string $studentId): array
    {
        $now = Carbon::now();
        $orders = Order::query()
            ->where('student_id', $studentId)
            ->with(['squad', 'package', 'learningSessions.schedule.teacher.user', 'learningSessions.material'])
            ->get();

        $sessions = $orders->flatMap(fn (Order $order) => $order->learningSessions);
        $unlockedSessionIds = EvaluationScore::query()
            ->where('student_id', $studentId)
            ->whereNotNull('pretest_score')
            ->whereIn('session_id', $sessions->pluck('session_id'))
            ->pluck('session_id')
            ->all();
        $upcoming = $sessions
            ->filter(fn (LearningSession $session) => $this->scheduledAt($session)?->greaterThanOrEqualTo($now))
            ->sortBy(fn (LearningSession $session) => $this->scheduledAt($session)?->timestamp ?? PHP_INT_MAX)
            ->first();

        $attendanceQuery = StudentAttendance::query()->where('student_id', $studentId);
        $attendanceTotal = (clone $attendanceQuery)->count();
        $attendancePresent = (clone $attendanceQuery)->whereIn('status', ['present', 'attended', 'hadir'])->count();

        $postTestAverage = EvaluationScore::query()
            ->where('student_id', $studentId)
            ->avg('posttest_score');
        $completedQuizzes = EvaluationScore::query()->where('student_id', $studentId)
            ->where(function ($query) {
                $query->whereNotNull('pretest_score')->orWhereNotNull('posttest_score');
            })
            ->count();
        $squad = $orders->filter(fn (Order $order) => $order->squad !== null)
            ->sortByDesc(fn (Order $order) => $order->order_date?->timestamp ?? 0)
            ->first()?->squad;

        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $weeklyAgenda = $sessions->filter(function (LearningSession $session) use ($weekStart, $weekEnd) {
            $scheduledAt = $this->scheduledAt($session);
            return $scheduledAt && $scheduledAt->betweenIncluded($weekStart, $weekEnd);
        })->sortBy(fn (LearningSession $session) => $this->scheduledAt($session)?->timestamp);

        $hasActiveSession = $sessions->contains(fn (LearningSession $session) =>
            in_array($session->status ?? $session->session_status, ['scheduled', 'ongoing', 'in_progress'], true)
        );

        $agenda = $weeklyAgenda->map(fn (LearningSession $session) =>
            $this->sessionPayload($session, in_array($session->session_id, $unlockedSessionIds, true))
        )->values();

        return [
            'has_active_session' => $hasActiveSession,
            'upcoming_session' => $upcoming ? $this->sessionPayload($upcoming, in_array($upcoming->session_id, $unlockedSessionIds, true)) : null,
            'squad_referral' => $squad ? [
                'squad_code' => $squad->squad_code,
                'status' => $squad->status,
                'target_capacity' => $squad->target_capacity,
                'member_count' => $squad->members()->count(),
            ] : null,
            'analytics' => [
                'attendance_percentage' => $attendanceTotal > 0 ? round(($attendancePresent / $attendanceTotal) * 100, 2) : 0,
                'avg_post_test' => $postTestAverage !== null ? round((float) $postTestAverage, 2) : null,
                'modul_terunduh' => null,
                'kuis_selesai' => $completedQuizzes,
            ],
            'weekly_agenda' => $agenda,
            'agenda' => $agenda,
        ];
    }

    private function sessionPayload(LearningSession $session, bool $preTestSubmitted): array
    {
        $session->loadMissing(['schedule.teacher.user', 'material', 'order.package']);
        $scheduledAt = $this->scheduledAt($session);

        return [
            'session_id' => $session->session_id,
            'scheduled_at' => $scheduledAt?->toISOString(),
            'duration_minutes' => $session->duration_minutes,
            'status' => $session->status ?? $session->session_status,
            'is_locked' => !$preTestSubmitted,
            'teacher_name' => $session->schedule?->teacher?->user?->full_name,
            'package_name' => $session->order?->package?->package_name,
            'material' => $session->material ? [
                'material_id' => $session->material->material_id,
                'title' => $session->material->title,
                'material_type' => $session->material->material_type,
            ] : null,
        ];
    }

    private function scheduledAt(LearningSession $session): ?Carbon
    {
        if ($session->scheduled_at) {
            return Carbon::parse($session->scheduled_at);
        }

        return $session->schedule?->start_time ? Carbon::parse($session->schedule->start_time) : null;
    }
}
