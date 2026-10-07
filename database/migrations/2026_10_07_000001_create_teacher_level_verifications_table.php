<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_level_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('teacher_id');
            $table->string('jenjang', 10);
            $table->string('status_verifikasi', 30)->default('menunggu_review');
            $table->timestamps();
            $table->unique(['teacher_id', 'jenjang']);
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_level_verifications');
    }
};
