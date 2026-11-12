<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\EvaluationScore;
use App\Models\LearningSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomPageController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()?->student;
        abort_unless($student, 403);

        $sessions = LearningSession::query()
            ->where(function ($query) use ($student) {
                $query->where('student_id', $student->student_id)
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery->where('student_id', $student->student_id));
            })
            ->with(['order.package', 'schedule', 'teacher.user', 'material'])
            ->get()
            ->sortBy(fn (LearningSession $session) => $this->scheduledTimestamp($session))
            ->values();

        $requestedSessionId = $request->query('session_id');
        if ($requestedSessionId) {
            $session = $sessions->firstWhere('session_id', $requestedSessionId);
            abort_unless($session, 404);
        } else {
            $session = $sessions->first(fn (LearningSession $item) => $this->scheduledTimestamp($item) >= now()->timestamp)
                ?? $sessions->first();
        }

        $evaluation = $session
            ? EvaluationScore::query()
                ->where('session_id', $session->session_id)
                ->where('student_id', $student->student_id)
                ->first()
            : null;

        return view('pages.classroom.show', [
            'sessions' => $sessions,
            'session' => $session,
            'evaluation' => $evaluation,
            'scheduledAt' => $session ? $this->scheduledAt($session) : null,
        ]);
    }

    private function scheduledTimestamp(LearningSession $session): int
    {
        $scheduledAt = $this->scheduledAt($session);

        return $scheduledAt?->timestamp ?? PHP_INT_MAX;
    }

    private function scheduledAt(LearningSession $session): ?Carbon
    {
        $value = $session->scheduled_at ?? $session->schedule?->start_time;

        return $value ? Carbon::parse($value) : null;
    }
}
