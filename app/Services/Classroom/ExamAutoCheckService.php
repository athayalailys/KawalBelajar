<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamAutoCheckService
{
    public function submitExam(LearningSession $session, string $studentId, string $type, array $answers): EvaluationScore
    {
        if (!in_array($type, ['pre_test', 'post_test'], true)) {
            throw ValidationException::withMessages(['exam_type' => 'Jenis ujian tidak valid.']);
        }

        $session->loadMissing(['order', 'schedule']);
        if (!$session->order || $session->order->student_id !== $studentId) {
            abort(403);
        }

        $scoreColumn = $type === 'pre_test' ? 'pretest_score' : 'posttest_score';
        $existing = EvaluationScore::query()
            ->where('session_id', $session->session_id)
            ->where('student_id', $studentId)
            ->whereNotNull($scoreColumn)
            ->exists();
        if ($existing) {
            throw ValidationException::withMessages(['exam_type' => 'Ujian ini sudah dikumpulkan.']);
        }

        $questionQuery = DB::table('question_banks')
            ->where('package_id', $session->order->package_id)
            ->where('exam_type', $type)
            ->where('session_number', $session->session_number ?? 1);
        if ($session->schedule?->teacher_id || $session->order->teacher_id) {
            $questionQuery->where('teacher_id', $session->schedule?->teacher_id ?? $session->order->teacher_id);
        }

        $questions = $questionQuery->get(['question_id', 'answer_key']);
        if ($questions->isEmpty()) {
            throw ValidationException::withMessages(['answers' => 'Soal belum tersedia untuk paket sesi ini.']);
        }

        $provided = collect($answers)->keyBy(fn (array $answer) => (string) $answer['question_id']);
        if ($provided->count() !== count($answers) || $provided->keys()->diff($questions->pluck('question_id'))->isNotEmpty()) {
            throw ValidationException::withMessages(['answers' => 'Jawaban memuat soal duplikat atau tidak sesuai sesi.']);
        }

        $correct = $questions->filter(function ($question) use ($provided) {
            $answer = $provided->get((string) $question->question_id);
            return $answer !== null && mb_strtolower(trim((string) $answer['answer']))
                === mb_strtolower(trim((string) $question->answer_key));
        })->count();

        $score = (int) round(($correct / $questions->count()) * 100);
        $evaluationScore = EvaluationScore::firstOrNew([
            'session_id' => $session->session_id,
            'student_id' => $studentId,
        ]);
        $evaluationScore->{$scoreColumn} = $score;
        $evaluationScore->created_at ??= Carbon::now();
        $evaluationScore->save();
        $evaluationScore->setAttribute('exam_type', $type);

        return $evaluationScore;
        
    }
}
