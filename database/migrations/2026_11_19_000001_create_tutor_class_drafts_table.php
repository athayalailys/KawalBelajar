<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tutor_class_drafts')) {
            return;
        }

        Schema::create('tutor_class_drafts', function (Blueprint $table) {
            $table->uuid('teacher_id')->primary();
            $table->uuid('package_id')->nullable()->unique();
            $table->json('draft_data');
            $table->timestamps();
            $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
            $table->foreign('package_id')->references('package_id')->on('module_packages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_class_drafts');
    }
};
