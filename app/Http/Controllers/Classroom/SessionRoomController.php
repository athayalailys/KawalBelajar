<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitExamRequest;
use App\Models\LearningSession;
use App\Models\QuestionBank;
use App\Models\User;
use App\Services\Classroom\ExamAutoCheckService;
use App\Services\Classroom\ZoomGatingService;
use Illuminate\Http\JsonResponse;

class SessionRoomController extends Controller
{
    public function examQuestions(\Illuminate\Http\Request $request, string $sessionId): JsonResponse
    {
        $validated = $request->validate(['exam_type' => ['required', 'string', 'in:pre_test,post_test']]);
        $student = $request->user()?->student;
        abort_unless($student, 403);
        $session = LearningSession::query()->with('order')->whereKey($sessionId)->firstOrFail();
        abort_unless($session->order?->student_id === $student->student_id, 403);

        $questions = QuestionBank::query()
            ->where('package_id', $session->order->package_id)
            ->where('teacher_id', $session->order->teacher_id)
            ->where('session_number', $session->session_number ?? 1)
            ->where('exam_type', $validated['exam_type'])
            ->get(['question_id', 'category', 'question_text', 'option_a', 'option_b', 'option_c', 'option_d'])
            ->map(fn (QuestionBank $question) => [
                'question_id' => $question->question_id,
                'category' => $question->category,
                'question_text' => $question->question_text,
                'options' => [
                    'a' => $question->option_a,
                    'b' => $question->option_b,
                    'c' => $question->option_c,
                    'd' => $question->option_d,
                ],
            ])->values();

        return response()->json(['success' => true, 'data' => [
            'session_id' => $session->session_id,
            'session_number' => $session->session_number ?? 1,
            'exam_type' => $validated['exam_type'],
            'questions' => $questions,
        ]]);
    }

    public function getRoomDetails(string $sessionId, ZoomGatingService $gatingService): JsonResponse
    {
        $session = LearningSession::query()->where('session_id', $sessionId)->firstOrFail();
        $user = request()->user();
        abort_unless($user instanceof User, 403);

        if (!$gatingService->canAccessMeetingUrl($session, $user)) {
            return response()->json([
                'message' => 'Anda wajib menyelesaikan Pre-Test sebelum dapat mengakses tautan ruang pembelajaran.',
                'data' => ['is_gated' => true],
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
        abort_unless($session->order?->student_id === $student->student_id, 403);

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
            'submitted_at' => $score->created_at?->toISOString(),
        ]]);
    }
}
