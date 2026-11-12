<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EvaluationScore;
use App\Models\LearningSession;
use App\Models\StudentAttendance;
use App\Services\Student\StudentDashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public function index(Request $request, StudentDashboardService $dashboardService): JsonResponse
    {
        $student = $request->user()?->student;
        abort_unless($student, 403);

        return response()->json([
            'success' => true,
            'data' => $dashboardService->getDashboardData($student->student_id),
        ]);
    }

    public function show(Request $request): View
    {
        $user = $request->user();
        $student = $user?->student;
        abort_unless($student, 403);

        $sessions = LearningSession::query()
            ->where('student_id', $student->student_id)
            ->with(['order.package', 'schedule', 'teacher.user', 'material'])
            ->get();

        $activeSessions = $sessions->reject(fn (LearningSession $session) => in_array(
            strtolower((string) ($session->session_status ?? $session->status)),
            ['completed', 'cancelled', 'canceled', 'expired'],
            true,
        ));

        $nearestSession = $activeSessions
            ->filter(fn (LearningSession $session) => $this->sessionStartTime($session)?->greaterThanOrEqualTo(now()))
            ->sortBy(fn (LearningSession $session) => $this->sessionStartTime($session)?->timestamp ?? PHP_INT_MAX)
            ->first()
            ?? $activeSessions->sortBy(fn (LearningSession $session) => $this->sessionStartTime($session)?->timestamp ?? PHP_INT_MAX)->first();

        $evaluations = EvaluationScore::query()
            ->where('student_id', $student->student_id)
            ->get(['pretest_score', 'posttest_score']);
        $attendanceRecords = StudentAttendance::query()
            ->where('student_id', $student->student_id)
            ->get(['status']);
        $presentCount = $attendanceRecords->whereIn('status', ['present', 'attended', 'hadir'])->count();
        $postTestScores = $evaluations->whereNotNull('posttest_score')->pluck('posttest_score');
        $completedProgress = $evaluations->filter(fn (EvaluationScore $evaluation) =>
            $evaluation->pretest_score !== null && $evaluation->posttest_score !== null
        )->map(fn (EvaluationScore $evaluation) => $evaluation->posttest_score - $evaluation->pretest_score);

        if ($nearestSession) {
            $nearestSession->setAttribute('start_time', $this->sessionStartTime($nearestSession));
            $nearestSession->setRelation('evaluation', EvaluationScore::query()
                ->where('session_id', $nearestSession->session_id)
                ->where('student_id', $student->student_id)
                ->first());
        }

        $completedSessionCount = $sessions->filter(fn (LearningSession $session) => in_array(
            strtolower((string) ($session->session_status ?? $session->status)),
            ['completed', 'complete', 'selesai'],
            true,
        ))->count();

        $stats = [
            'attendance_rate' => $attendanceRecords->isNotEmpty()
                ? round(($presentCount / $attendanceRecords->count()) * 100)
                : 0,
            'attendance_total' => $attendanceRecords->count(),
            'average_score' => $postTestScores->isNotEmpty() ? round($postTestScores->avg(), 1) : null,
            'total_sessions' => $completedSessionCount,
            'progress_gain' => $completedProgress->isNotEmpty() ? round($completedProgress->avg(), 1) : null,
        ];

        $nearestSessionStatus = $nearestSession
            ? (in_array(strtolower((string) ($nearestSession->session_status ?? $nearestSession->status)), ['in_progress', 'ongoing', 'berlangsung'], true)
                || $nearestSession->hasStarted()
                ? 'Sesi berlangsung'
                : 'Menunggu waktu sesi')
            : null;

        return view('pages.student.dashboard', compact(
            'user',
            'student',
            'nearestSession',
            'nearestSessionStatus',
            'stats',
        ));
    }

    private function sessionStartTime(LearningSession $session): ?Carbon
    {
        $startTime = $session->scheduled_at ?? $session->schedule?->start_time;

        return $startTime ? Carbon::parse($startTime) : null;
    }
}
