<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->uuid('teacher_id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('university', 100)->nullable();
            $table->string('study_program', 100)->nullable();
            $table->string('semester', 50)->nullable();
            $table->string('focus_subject', 150)->nullable();
            $table->json('requested_levels')->nullable();
            $table->json('approved_levels')->nullable();
            $table->string('cv_url')->nullable();
            $table->string('identity_document_url')->nullable();
            $table->string('transcript_url')->nullable();
            $table->string('certificate_url')->nullable();
            $table->string('video_link')->nullable();
            $table->string('cv_status', 20)->default('pending');
            $table->text('review_note')->nullable();
            $table->string('teacher_level', 50)->nullable();
            $table->string('default_location', 150)->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('admin_id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('employee_id_number')->nullable();
            $table->string('position')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('teachers');
    }
};
