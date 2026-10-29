<?php

namespace App\Services\Classroom;

use App\Models\EvaluationScore;
use App\Models\LearningSession;
use App\Models\StudentAttendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    public function submitSessionEvaluation(LearningSession $session, array $data): array
    {
        $session->loadMissing('order.squad.members');
        $allowedStudentIds = collect([
            $session->student_id,
            $session->order?->student_id,
        ])->merge($session->order?->squad?->members->pluck('student_id') ?? [])->filter()->unique();

        foreach ($data['attendances'] as $attendanceData) {
            abort_unless($allowedStudentIds->contains($attendanceData['student_id']), 403);
        }

        return DB::transaction(function () use ($session, $data): array {
            $submittedAt = Carbon::now();
            $attendances = [];
            $evaluations = [];

            foreach ($data['attendances'] as $attendanceData) {
                $studentId = $attendanceData['student_id'];

                $attendances[] = StudentAttendance::updateOrCreate(
                    [
                        'session_id' => $session->session_id,
                        'student_id' => $studentId,
                    ],
                    [
                        'attendance_time' => $submittedAt,
                        'status' => $attendanceData['status'],
                    ]
                );

                $evaluation = EvaluationScore::firstOrNew([
                    'session_id' => $session->session_id,
                    'student_id' => $studentId,
                ]);

                if (array_key_exists('posttest_score', $data) && $data['posttest_score'] !== null) {
                    $evaluation->posttest_score = (int) $data['posttest_score'];

                    if ($evaluation->pretest_score !== null) {
                        $evaluation->progress_percentage = (int) $data['posttest_score'] - (int) $evaluation->pretest_score;
                    }
                }

                if (array_key_exists('teacher_notes', $data)) {
                    $evaluation->teacher_notes = $data['teacher_notes'];
                }

                $evaluation->created_at ??= $submittedAt;
                $evaluation->save();
                $evaluations[] = $evaluation;
            }

            return [
                'attendances' => $attendances,
                'evaluations' => $evaluations,
            ];
        });
    }
}
