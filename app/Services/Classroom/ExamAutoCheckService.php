<?php

namespace App\Services\Classroom;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExamAutoCheckService
{
    public function submitExam(LearningSession $session, string $studentId, string $type, array $answers): EvaluationScore
    {
        if (!in_array($type, ['pre_test', 'post_test'], true)) {
            throw ValidationException::withMessages(['exam_type' => 'Jenis ujian tidak valid.']);
        }

        $session->loadMissing(['order', 'schedule']);
        if ($session->student_id !== $studentId && $session->order?->student_id !== $studentId) {
            abort(403);
        }

        if (!$session->hasStarted()) {
            abort(403, 'Jadwal bimbingan belum dimulai. Ujian belum dapat dikerjakan.');
        }

        if ($type === 'post_test' && !EvaluationScore::query()
            ->where('session_id', $session->session_id)
            ->where('student_id', $studentId)
            ->whereNotNull('pretest_score')
            ->exists()) {
            abort(403, 'Selesaikan Pre-Test sebelum mengerjakan Post-Test.');
        }

        if ($type === 'post_test' && !in_array($session->session_status ?? $session->status, ['completed', 'selesai'], true)) {
            abort(403, 'Tes akhir tersedia setelah tutor menyelesaikan sesi.');
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
        $evaluationScore = DB::transaction(function () use ($questions, $provided, $session, $studentId, $scoreColumn, $score) {
            $evaluationScore = EvaluationScore::query()->firstOrNew([
                'session_id' => $session->session_id,
                'student_id' => $studentId,
            ]);
            $evaluationScore->{$scoreColumn} = $score;
            $evaluationScore->created_at ??= Carbon::now();

            if ($scoreColumn === 'posttest_score' && $evaluationScore->pretest_score !== null) {
                $pretestScore = (int) $evaluationScore->pretest_score;
                $evaluationScore->progress_percentage = $pretestScore === 0
                    ? $score
                    : round((($score - $pretestScore) / $pretestScore) * 100, 2);
            }
            $evaluationScore->save();

            foreach ($questions as $question) {
                $answer = $provided->get((string) $question->question_id);
                $answerText = (string) ($answer['answer'] ?? '');
                $isCorrect = $answer !== null && mb_strtolower(trim($answerText))
                    === mb_strtolower(trim((string) $question->answer_key));

                DB::table('student_answers')->updateOrInsert(
                    [
                        'session_id' => $session->session_id,
                        'student_id' => $studentId,
                        'question_id' => $question->question_id,
                    ],
                    [
                        'answer_id' => (string) Str::uuid(),
                        'selected_answer' => $answerText,
                        'student_answer' => $answerText,
                        'is_correct' => $isCorrect,
                        'answered_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );
            }

            return $evaluationScore->refresh();
        });
        $evaluationScore->setAttribute('exam_type', $type);

        return $evaluationScore;
        
    }
}
