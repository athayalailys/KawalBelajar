<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('learning_sessions')) {
            $columns = [
                'student_id' => fn (Blueprint $table) => $table->uuid('student_id')->nullable(),
                'teacher_id' => fn (Blueprint $table) => $table->uuid('teacher_id')->nullable(),
                'scheduled_at' => fn (Blueprint $table) => $table->dateTime('scheduled_at')->nullable(),
                'duration_minutes' => fn (Blueprint $table) => $table->unsignedInteger('duration_minutes')->nullable(),
                'learning_method' => fn (Blueprint $table) => $table->string('learning_method', 30)->nullable(),
                'student_address' => fn (Blueprint $table) => $table->text('student_address')->nullable(),
                'latitude' => fn (Blueprint $table) => $table->decimal('latitude', 10, 7)->nullable(),
                'longitude' => fn (Blueprint $table) => $table->decimal('longitude', 10, 7)->nullable(),
                'address_landmark' => fn (Blueprint $table) => $table->string('address_landmark')->nullable(),
                'absensi_masuk_guru' => fn (Blueprint $table) => $table->dateTime('absensi_masuk_guru')->nullable(),
                'absensi_keluar_guru' => fn (Blueprint $table) => $table->dateTime('absensi_keluar_guru')->nullable(),
                'status' => fn (Blueprint $table) => $table->string('status', 30)->nullable(),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('learning_sessions', $column)) {
                    Schema::table('learning_sessions', $definition);
                }
            }
        }

        if (!Schema::hasTable('nilai_evaluasi')) {
            Schema::create('nilai_evaluasi', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('learning_session_id');
                $table->uuid('student_id');
                $table->enum('exam_type', ['pre_test', 'post_test']);
                $table->decimal('score', 5, 2);
                $table->dateTime('submitted_at');
                $table->unique(['learning_session_id', 'student_id', 'exam_type'], 'nilai_evaluasi_attempt_unique');
                $table->foreign('learning_session_id')->references('session_id')->on('learning_sessions')->cascadeOnDelete();
                $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('nilai_evaluasi')) {
            Schema::dropIfExists('nilai_evaluasi');
        }

    }
};
