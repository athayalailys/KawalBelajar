<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('learning_sessions', 'teacher_clock_in')) {
                $table->timestamp('teacher_clock_in')->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'teacher_clock_out')) {
                $table->timestamp('teacher_clock_out')->nullable();
            }

            if (!Schema::hasColumn('learning_sessions', 'zoom_meeting_url')) {
                $table->string('zoom_meeting_url')->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'onsite_address')) {
                $table->text('onsite_address')->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'student_address')) {
                $table->text('student_address')->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'address_landmark')) {
                $table->string('address_landmark')->nullable();
            }
            if (!Schema::hasColumn('learning_sessions', 'session_status')) {
                $table->string('session_status')->default('scheduled');
            }
            if (!Schema::hasColumn('learning_sessions', 'status')) {
                $table->string('status')->default('scheduled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('learning_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'absensi_masuk_guru',
                'absensi_keluar_guru',
                'teacher_clock_in',
                'teacher_clock_out',
                'zoom_meeting_url',
                'onsite_address',
                'student_address',
                'latitude',
                'longitude',
                'address_landmark',
                'session_status',
                'status',
            ]);
        });
    }
};