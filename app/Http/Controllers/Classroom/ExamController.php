<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\SubmitExamRequest;
use App\Models\LearningSession;
use App\Services\Classroom\ExamAutoCheckService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private ExamAutoCheckService $examService
    ) {}

    public function submitExam(SubmitExamRequest $request, string $sessionId): JsonResponse
    {
        $session = LearningSession::where('session_id', $sessionId)->firstOrFail();

        // Mengambil UUID student dari relasi user/student
        $studentId = $request->user()->student?->student_id ?? (string) $request->user()->id;

        $examType = $request->input('exam_type', 'pre_test');

        try {
            $evaluationScore = $this->examService->submitExam(
                $session,
                $studentId,
                $examType,
                $request->input('answers')
            );

            // 1. Hitung skor terlebih dahulu (contoh kalkulasi jawaban benar)
        return response()->json([
            'message' => 'Jawaban ' . strtoupper($examType) . ' berhasil dikirim.',
            'data' => [
                'session_id'   => $evaluationScore->session_id,
                'exam_type'    => $examType,
                'score'        => (int) ($examType === 'pre_test' ? $evaluationScore->pretest_score : $evaluationScore->posttest_score),
                'submitted_at' => $evaluationScore->created_at?->toIso8601String(),
            ]
        ], 200);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Gagal mengumpulkan kuis.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
