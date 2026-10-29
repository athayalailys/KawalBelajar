<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Availability Slots Tutor
        if (!Schema::hasTable('teacher_availability_slots')) {
            Schema::create('teacher_availability_slots', function (Blueprint $table) {
                $table->uuid('slot_id')->primary();
                $table->uuid('teacher_id');
                $table->string('day_of_week'); // Monday - Sunday
                $table->time('start_time');
                $table->time('end_time');
                $table->string('learning_method')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->index(['teacher_id', 'day_of_week', 'start_time']);
            });
        } elseif (!Schema::hasColumn('teacher_availability_slots', 'learning_method')) {
            Schema::table('teacher_availability_slots', function (Blueprint $table) {
                $table->string('learning_method')->nullable();
            });
        }

        // 2. Squad Patungan
        if (!Schema::hasTable('squads')) {
            Schema::create('squads', function (Blueprint $table) {
                $table->uuid('squad_id')->primary();
                $table->string('squad_code')->unique(); // e.g. SQUAD-ALYA-829
                $table->uuid('host_student_id');
                $table->uuid('teacher_id');
                $table->uuid('package_id');
                $table->uuid('selected_slot_id');
                $table->string('learning_method'); // home_visit / online_zoom
                $table->integer('target_capacity');
                $table->decimal('discount_rate', 5, 2); // e.g. 15.00 (%)
                $table->string('status')->default('open'); // open, full, completed
                $table->timestamps();
            });
        } else {
            Schema::table('squads', function (Blueprint $table) {
                if (!Schema::hasColumn('squads', 'package_id')) {
                    $table->uuid('package_id')->nullable();
                }
                if (!Schema::hasColumn('squads', 'selected_slot_id')) {
                    $table->uuid('selected_slot_id')->nullable();
                }
            });
        }

        // 3. Members Squad
        if (!Schema::hasTable('squad_members')) {
            Schema::create('squad_members', function (Blueprint $table) {
                $table->uuid('member_id')->primary();
                $table->uuid('squad_id');
                $table->uuid('student_id');
                $table->uuid('order_id');
                $table->timestamps();
                $table->unique(['squad_id', 'student_id']);
                $table->unique('order_id');
            });
        }

        // 4. Buat atau Update Tabel Orders
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->uuid('order_id')->primary();
                $table->uuid('student_id');
                $table->uuid('teacher_id');
                $table->uuid('package_id');
                $table->uuid('squad_id')->nullable();
                $table->string('learning_method');
                $table->integer('session_package')->default(1);
                $table->uuid('selected_slot_id')->nullable();
                $table->decimal('total_amount', 12, 2);
                $table->string('payment_status')->default('pending');
                $table->string('invoice_number')->nullable()->unique();
                $table->string('xendit_invoice_id')->nullable();
                $table->text('xendit_payment_url')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('order_date')->nullable();
                $table->string('order_status')->default('pending');
                $table->timestamps();
                $table->index(['teacher_id', 'selected_slot_id', 'payment_status']);
            });
        } else {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'teacher_id')) {
                    $table->uuid('teacher_id')->nullable()->after('student_id');
                }
                if (!Schema::hasColumn('orders', 'package_id')) {
                    $table->uuid('package_id')->nullable()->after('teacher_id');
                }
                if (!Schema::hasColumn('orders', 'squad_id')) {
                    $table->uuid('squad_id')->nullable()->after('package_id');
                }
                if (!Schema::hasColumn('orders', 'learning_method')) {
                    $table->string('learning_method')->after('squad_id');
                }
                if (!Schema::hasColumn('orders', 'session_package')) {
                    $table->integer('session_package')->default(1)->after('learning_method');
                }
                if (!Schema::hasColumn('orders', 'selected_slot_id')) {
                    $table->uuid('selected_slot_id')->nullable()->after('session_package');
                }
                if (!Schema::hasColumn('orders', 'xendit_invoice_id')) {
                    $table->string('xendit_invoice_id')->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('orders', 'xendit_payment_url')) {
                    $table->text('xendit_payment_url')->nullable()->after('xendit_invoice_id');
                }
                if (!Schema::hasColumn('orders', 'payment_status')) {
                    $table->string('payment_status')->default('pending');
                }
                if (!Schema::hasColumn('orders', 'invoice_number')) {
                    $table->string('invoice_number')->nullable()->unique();
                }
                if (!Schema::hasColumn('orders', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable();
                }
                if (!Schema::hasColumn('orders', 'total_amount')) {
                    $table->decimal('total_amount', 12, 2)->default(0);
                }
                if (!Schema::hasColumn('orders', 'order_date')) {
                    $table->timestamp('order_date')->nullable();
                }
                if (!Schema::hasColumn('orders', 'order_status')) {
                    $table->string('order_status')->default('pending');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('squad_members');
        Schema::dropIfExists('squads');
        Schema::dropIfExists('teacher_availability_slots');

        if (Schema::hasTable('orders')) {
            $columns = collect([
                'squad_id', 'learning_method', 'session_package',
                'selected_slot_id', 'payment_status', 'invoice_number', 'xendit_invoice_id',
                'xendit_payment_url', 'expires_at',
            ])->filter(fn (string $column) => Schema::hasColumn('orders', $column))->values()->all();

            if ($columns !== []) {
                Schema::table('orders', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
};
