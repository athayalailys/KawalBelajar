<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\SubmitEvaluationRequest;
use App\Models\LearningSession;
use App\Services\Classroom\EvaluationService;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    public function __construct(private EvaluationService $evaluationService)
    {
    }

    public function store(SubmitEvaluationRequest $request, string $sessionId): JsonResponse
    {
        $session = LearningSession::query()
            ->with('order')
            ->where('session_id', $sessionId)
            ->firstOrFail();

        $teacherId = $request->user()?->teacher?->teacher_id;
        abort_unless($teacherId && $session->teacher_id === $teacherId, 403);

        $result = $this->evaluationService->submitSessionEvaluation($session, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Evaluasi pembelajaran dan absensi siswa berhasil disimpan.',
            'data' => [
                'session_id' => $session->session_id,
                'attendance_count' => count($result['attendances']),
                'evaluations' => collect($result['evaluations'])->map(fn ($evaluation) => [
                    'student_id' => $evaluation->student_id,
                    'posttest_score' => $evaluation->posttest_score,
                    'progress_percentage' => $evaluation->progress_percentage,
                ])->values(),
            ],
        ], 200);
    }
}
