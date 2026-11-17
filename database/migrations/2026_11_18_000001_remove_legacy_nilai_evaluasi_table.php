<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nilai_evaluasi')) {
            return;
        }

        // Preserve any legacy score not already moved by the compatibility migration.
        if (Schema::hasTable('evaluation_scores')) {
            foreach (DB::table('nilai_evaluasi')->orderBy('submitted_at')->get() as $legacyScore) {
                $scoreColumn = $legacyScore->exam_type === 'pre_test' ? 'pretest_score' : 'posttest_score';
                $evaluation = DB::table('evaluation_scores')
                    ->where('session_id', $legacyScore->learning_session_id)
                    ->where('student_id', $legacyScore->student_id)
                    ->first();

                if ($evaluation) {
                    if ($evaluation->{$scoreColumn} === null) {
                        DB::table('evaluation_scores')
                            ->where('evaluation_id', $evaluation->evaluation_id)
                            ->update([$scoreColumn => (int) $legacyScore->score]);
                    }

                    continue;
                }

                DB::table('evaluation_scores')->insert([
                    'evaluation_id' => (string) \Illuminate\Support\Str::uuid(),
                    'session_id' => $legacyScore->learning_session_id,
                    'student_id' => $legacyScore->student_id,
                    $scoreColumn => (int) $legacyScore->score,
                    'created_at' => $legacyScore->submitted_at,
                ]);
            }
        }

        Schema::dropIfExists('nilai_evaluasi');
    }

    public function down(): void
    {
        // Legacy scores now live in evaluation_scores; recreating the old schema
        // would split the canonical evaluation data again.
    }
};
