<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nilai_evaluasi')) {
            Schema::create('evaluation_scores', function (Blueprint $table) {
                $table->uuid('evaluation_id')->primary();
                $table->foreignUuid('session_id')->references('session_id')->on('learning_sessions');
                $table->foreignUuid('student_id')->references('student_id')->on('students');
                $table->integer('pretest_score')->nullable();
                $table->integer('posttest_score')->nullable();
                $table->decimal('progress_percentage', 5, 2)->nullable();
                $table->text('teacher_notes')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('student_answers')) {
            Schema::create('student_answers', function (Blueprint $table) {
                $table->id();
                $table->string('session_id');
                $table->string('student_id');
                $table->string('question_id');
                $table->string('selected_answer');
                $table->boolean('is_correct')->default(false);
                $table->timestamps();

                $table->foreign('session_id')->references('session_id')->on('learning_sessions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
        Schema::dropIfExists('nilai_evaluasi');
    }
};
