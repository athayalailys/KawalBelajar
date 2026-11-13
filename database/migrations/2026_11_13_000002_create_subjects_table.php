<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subjects')) {
            return;
        }

        Schema::create('subjects', function (Blueprint $table) {
            $table->uuid('subject_id')->primary();
            $table->string('name', 100);
            $table->string('grade_level', 20)->index();
            $table->string('category', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['grade_level', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
