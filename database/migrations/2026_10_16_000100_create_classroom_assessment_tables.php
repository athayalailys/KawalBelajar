<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('question_banks')) {
            Schema::create('question_banks', function (Blueprint $table) {
                $table->uuid('question_id')->primary();
                $table->uuid('teacher_id');
                $table->uuid('package_id');
                $table->enum('exam_type', ['pre_test', 'post_test']);
                $table->string('category', 30);
                $table->text('question_text');
                $table->text('answer_key');
                $table->index(['package_id', 'teacher_id', 'category']);
                $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
            });
        } elseif (!Schema::hasColumn('question_banks', 'exam_type')) {
            Schema::table('question_banks', function (Blueprint $table) {
                $table->enum('exam_type', ['pre_test', 'post_test'])->nullable();
            });
        }

        if (!Schema::hasTable('student_attendances')) {
            Schema::create('student_attendances', function (Blueprint $table) {
                $table->uuid('attendance_id')->primary();
                $table->uuid('session_id');
                $table->uuid('student_id');
                $table->dateTime('attendance_time')->nullable();
                $table->string('status', 30)->default('present');
                $table->index(['student_id', 'status']);
                $table->foreign('session_id')->references('session_id')->on('learning_sessions')->cascadeOnDelete();
                $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['student_attendances', 'question_banks'] as $table) {
            if (Schema::hasTable($table) && !DB::table($table)->exists()) {
                Schema::drop($table);
            }
        }
    }
};
