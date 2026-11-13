<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_module_downloads')) {
            return;
        }

        Schema::create('student_module_downloads', function (Blueprint $table) {
            $table->uuid('download_id')->primary();
            $table->uuid('student_id');
            $table->uuid('session_id');
            $table->uuid('material_id');
            $table->timestamp('downloaded_at');
            $table->unique(['student_id', 'session_id', 'material_id'], 'student_module_download_once');
            $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            $table->foreign('session_id')->references('session_id')->on('learning_sessions')->cascadeOnDelete();
            $table->foreign('material_id')->references('material_id')->on('learning_materials')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_module_downloads');
    }
};
