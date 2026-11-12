<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitExamRequest;
use App\Models\LearningSession;
use App\Models\EvaluationScore;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\Classroom\ExamAutoCheckService;
use App\Services\Classroom\ZoomGatingService;
use Illuminate\Http\JsonResponse;

class SessionRoomController extends Controller
{
    public function getExam(string $sessionId, string $examType): JsonResponse
    {
        abort_unless(in_array($examType, ['pre_test', 'post_test'], true), 404);

        $student = request()->user()?->student;
        abort_unless($student, 403);

        $session = LearningSession::query()
            ->with(['order', 'schedule'])
            ->where('session_id', $sessionId)
            ->firstOrFail();
        abort_unless(
            $session->student_id === $student->student_id
                || $session->order?->student_id === $student->student_id,
            403
        );
        abort_unless($session->hasStarted(), 403, 'Jadwal bimbingan belum dimulai. Ujian belum dapat diakses.');

        $evaluationScore = EvaluationScore::query()
            ->where('session_id', $session->session_id)
            ->where('student_id', $student->student_id)
            ->first();
        if ($examType === 'post_test') {
            abort_unless($evaluationScore?->pretest_score !== null, 403, 'Selesaikan Pre-Test sebelum mengerjakan Post-Test.');
        }

        $teacherId = $session->schedule?->teacher_id ?? $session->teacher_id ?? $session->order->teacher_id;
        $questionsQuery = QuestionBank::query()
            ->where('package_id', $session->order->package_id)
            ->where('exam_type', $examType);

        if ($teacherId) {
            $questionsQuery->where('teacher_id', $teacherId);
        }

        $questions = $questionsQuery->orderBy('question_id')->get([
            'question_id',
            'category',
            'question_text',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
        ]);

        $score = $examType === 'pre_test'
            ? $evaluationScore?->pretest_score
            : $evaluationScore?->posttest_score;

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->session_id,
                'exam_type' => $examType,
                'already_submitted' => $score !== null,
                'score' => $score === null ? null : (int) $score,
                'duration_minutes' => $examType === 'pre_test'
                    ? (max(1, (int) ceil($questions->count() / 5)) * 5)
                    : null,
                'questions' => $questions,
            ],
        ]);
    }

    public function getRoomDetails(string $sessionId, ZoomGatingService $gatingService): JsonResponse
    {
        $session = LearningSession::query()->with('order')->where('session_id', $sessionId)->firstOrFail();
        $user = request()->user();
        abort_unless($user instanceof User, 403);

        $studentId = $user->student?->student_id;
        $teacherId = $user->teacher?->teacher_id;
        $isStudentOwner = $studentId !== null
            && ($session->student_id === $studentId || $session->order?->student_id === $studentId);
        $isAssignedTeacher = $teacherId !== null && $session->teacher_id === $teacherId;
        abort_unless($isStudentOwner || $isAssignedTeacher, 403);

        if (!$gatingService->canAccessMeetingUrl($session, $user)) {
            $message = $user->student && !$session->hasStarted()
                ? 'Jadwal bimbingan belum dimulai.'
                : 'Anda wajib menyelesaikan Pre-Test sebelum dapat mengakses tautan ruang pembelajaran.';

            return response()->json([
                'message' => $message,
                'data' => [
                    'is_gated' => true,
                    'gate_reason' => $user->student && !$session->hasStarted() ? 'not_started' : 'pretest_required',
                ],
            ], 403);
        }

        return response()->json([
            'message' => 'Akses ruang sesi berhasil dimuat.',
            'data' => [
                'session_id' => $session->session_id,
                'zoom_meeting_url' => $session->zoom_meeting_url,
                'is_gated' => false,
            ],
        ], 200);
    }

    public function roomAccess(string $sessionId, ZoomGatingService $gatingService): JsonResponse
    {
        $student = request()->user()?->student;
        abort_unless($student, 403);
        $session = LearningSession::query()->with(['order', 'schedule'])->whereKey($sessionId)->firstOrFail();
        abort_unless(
            $session->student_id === $student->student_id
                || $session->order?->student_id === $student->student_id,
            403
        );

        return response()->json(['success' => true, 'data' => $gatingService->checkSessionAccess($session, $student->student_id)]);
    }

    public function submitExam(SubmitExamRequest $request, string $sessionId, ExamAutoCheckService $examService): JsonResponse
    {
        $student = $request->user()?->student;
        abort_unless($student, 403);
        $session = LearningSession::query()->with(['order', 'schedule'])->whereKey($sessionId)->firstOrFail();
        $examType = $request->validated('exam_type');
        $score = $examService->submitExam(
            $session,
            $student->student_id,
            $examType,
            $request->validated('answers'),
        );

        return response()->json(['success' => true, 'data' => [
            'session_id' => $score->session_id,
            'exam_type' => $examType,
            'score' => (int) ($examType === 'pre_test' ? $score->pretest_score : $score->posttest_score),
            'pretest_score' => $score->pretest_score,
            'posttest_score' => $score->posttest_score,
            'progress_percentage' => $score->progress_percentage,
            'submitted_at' => $score->created_at?->toISOString(),
        ]]);
    }
}
