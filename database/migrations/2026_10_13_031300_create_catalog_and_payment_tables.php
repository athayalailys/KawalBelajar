<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('module_packages')) {
            Schema::create('module_packages', function (Blueprint $table) {
                $table->uuid('package_id')->primary();
                $table->string('package_name');
                $table->string('grade_level', 20)->index();
                $table->text('description')->nullable();
                $table->decimal('base_price', 12, 2)->default(0);
                $table->boolean('is_active')->default(true);
            });
        }

        if (!Schema::hasTable('master_tariffs')) {
            Schema::create('master_tariffs', function (Blueprint $table) {
                $table->uuid('rate_id')->primary();
                $table->uuid('package_id');
                $table->string('teacher_level', 100);
                $table->string('learning_method', 30)->nullable();
                $table->decimal('category_rate', 12, 2);
                $table->uuid('created_by_admin_id')->nullable();
                $table->unique(['package_id', 'teacher_level', 'learning_method'], 'master_tariffs_package_level_method_unique');
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('learning_materials')) {
            Schema::create('learning_materials', function (Blueprint $table) {
                $table->uuid('material_id')->primary();
                $table->uuid('teacher_id');
                $table->uuid('package_id');
                $table->string('title');
                $table->string('material_type', 50);
                $table->text('file_or_url')->nullable();
                $table->unsignedInteger('video_duration_minutes')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['teacher_id', 'package_id']);
                $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('learning_schedules')) {
            Schema::create('learning_schedules', function (Blueprint $table) {
                $table->uuid('schedule_id')->primary();
                $table->uuid('teacher_id');
                $table->uuid('package_id');
                $table->string('method', 30);
                $table->string('location')->nullable();
                $table->dateTime('start_time');
                $table->dateTime('end_time');
                $table->index(['teacher_id', 'package_id', 'method']);
                $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('learning_sessions')) {
            Schema::create('learning_sessions', function (Blueprint $table) {
                $table->uuid('session_id')->primary();
                $table->uuid('order_id');
                $table->uuid('schedule_id')->nullable();
                $table->uuid('material_id')->nullable();
                $table->string('session_status', 30)->default('pending');
                $table->text('zoom_meeting_url')->nullable();
                $table->text('onsite_address')->nullable();
                $table->dateTime('teacher_clock_in')->nullable();
                $table->dateTime('teacher_clock_out')->nullable();
                $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
                $table->foreign('schedule_id')->references('schedule_id')->on('learning_schedules')->nullOnDelete();
                $table->foreign('material_id')->references('material_id')->on('learning_materials')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->uuid('payment_id')->primary();
                $table->uuid('order_id')->unique();
                $table->string('xendit_invoice_id')->nullable()->unique();
                $table->string('payment_method', 50)->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('payment_status', 30)->default('pending');
                $table->dateTime('paid_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->foreign('order_id')->references('order_id')->on('orders')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['payments', 'learning_sessions', 'learning_schedules', 'learning_materials', 'master_tariffs', 'module_packages'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                return;
            }
        }

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }
    }
};
