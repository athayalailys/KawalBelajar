<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_class_session_contents', function (Blueprint $table) {
            $table->uuid('content_id')->primary();
            $table->uuid('package_id');
            $table->unsignedTinyInteger('session_number');
            $table->string('topic', 255)->nullable();
            $table->text('learning_target')->nullable();
            $table->timestamps();
            $table->unique(['package_id', 'session_number']);
            $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
        });

        Schema::table('learning_materials', function (Blueprint $table) {
            $table->unsignedTinyInteger('session_number')->default(1)->after('package_id');
        });

        Schema::table('question_banks', function (Blueprint $table) {
            $table->unsignedTinyInteger('session_number')->default(1)->after('package_id');
            $table->index(['package_id', 'session_number', 'exam_type'], 'question_banks_package_session_exam_index');
        });

        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->decimal('teacher_clock_in_latitude', 10, 7)->nullable();
            $table->decimal('teacher_clock_in_longitude', 10, 7)->nullable();
            $table->decimal('teacher_clock_out_latitude', 10, 7)->nullable();
            $table->decimal('teacher_clock_out_longitude', 10, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropIndex('question_banks_package_session_exam_index');
            $table->dropColumn('session_number');
        });
        Schema::table('learning_materials', fn (Blueprint $table) => $table->dropColumn('session_number'));
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_clock_in_latitude', 'teacher_clock_in_longitude',
                'teacher_clock_out_latitude', 'teacher_clock_out_longitude',
            ]);
        });
        Schema::dropIfExists('tutor_class_session_contents');
    }
};
