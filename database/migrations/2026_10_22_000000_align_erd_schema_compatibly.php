<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $this->createParentsAndDocuments();
        $this->addColumns('teachers', [
            'cv_document_id' => fn (Blueprint $table) => $table->uuid('cv_document_id')->nullable(),
            'rejection_reason' => fn (Blueprint $table) => $table->text('rejection_reason')->nullable(),
            'verified_levels' => fn (Blueprint $table) => $table->json('verified_levels')->nullable(),
            'wallet_balance' => fn (Blueprint $table) => $table->decimal('wallet_balance', 12, 2)->default(0),
        ]);
        $this->addColumns('students', [
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'address_landmark' => fn (Blueprint $table) => $table->text('address_landmark')->nullable(),
        ]);
        $this->addColumns('teacher_availability_slots', [
            'is_available' => fn (Blueprint $table) => $table->boolean('is_available')->default(true),
        ]);
        $this->addColumns('module_packages', [
            'teacher_id' => fn (Blueprint $table) => $table->uuid('teacher_id')->nullable(),
            'subject_name' => fn (Blueprint $table) => $table->string('subject_name', 100)->nullable(),
            'learning_method' => fn (Blueprint $table) => $table->string('learning_method', 30)->nullable(),
            'max_capacity' => fn (Blueprint $table) => $table->unsignedInteger('max_capacity')->nullable(),
            'target_learning' => fn (Blueprint $table) => $table->text('target_learning')->nullable(),
            'supported_session_packages' => fn (Blueprint $table) => $table->string('supported_session_packages', 50)->nullable(),
            'is_published' => fn (Blueprint $table) => $table->boolean('is_published')->default(false),
            'is_open' => fn (Blueprint $table) => $table->boolean('is_open')->default(true),
        ]);
        $this->addColumns('master_tariffs', [
            'tariff_id' => fn (Blueprint $table) => $table->uuid('tariff_id')->nullable()->unique(),
            'grade_level' => fn (Blueprint $table) => $table->string('grade_level', 50)->nullable(),
            'method' => fn (Blueprint $table) => $table->string('method', 30)->nullable(),
            'base_rate' => fn (Blueprint $table) => $table->decimal('base_rate', 12, 2)->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
        ]);
        $this->addColumns('question_banks', [
            'option_a' => fn (Blueprint $table) => $table->text('option_a')->nullable(),
            'option_b' => fn (Blueprint $table) => $table->text('option_b')->nullable(),
            'option_c' => fn (Blueprint $table) => $table->text('option_c')->nullable(),
            'option_d' => fn (Blueprint $table) => $table->text('option_d')->nullable(),
        ]);
        $this->addColumns('learning_schedules', [
            'squad_id' => fn (Blueprint $table) => $table->uuid('squad_id')->nullable(),
            'slot_id' => fn (Blueprint $table) => $table->uuid('slot_id')->nullable(),
            'schedule_status' => fn (Blueprint $table) => $table->string('schedule_status', 20)->default('scheduled'),
        ]);
        $this->addColumns('learning_sessions', [
            'order_id' => fn (Blueprint $table) => $table->uuid('order_id')->nullable(),
            'session_number' => fn (Blueprint $table) => $table->unsignedInteger('session_number')->nullable(),
        ]);
        $this->addColumns('orders', [
            'squad_code_used' => fn (Blueprint $table) => $table->string('squad_code_used', 20)->nullable(),
            'method' => fn (Blueprint $table) => $table->string('method', 30)->nullable(),
            'learning_format' => fn (Blueprint $table) => $table->string('learning_format', 30)->nullable(),
            'session_package_count' => fn (Blueprint $table) => $table->unsignedInteger('session_package_count')->nullable(),
            'personal_amount' => fn (Blueprint $table) => $table->decimal('personal_amount', 12, 2)->nullable(),
            'discount_percentage' => fn (Blueprint $table) => $table->decimal('discount_percentage', 5, 2)->nullable(),
        ]);
        $this->addColumns('payments', [
            'expired_at' => fn (Blueprint $table) => $table->timestamp('expired_at')->nullable(),
        ]);
        $this->addColumns('student_answers', [
            'answer_id' => fn (Blueprint $table) => $table->uuid('answer_id')->nullable()->unique(),
            'student_answer' => fn (Blueprint $table) => $table->text('student_answer')->nullable(),
            'answered_at' => fn (Blueprint $table) => $table->timestamp('answered_at')->nullable(),
        ]);

        $this->backfillCompatibilityValues();

        $this->createMissingTables();
        $this->migrateLegacyEvaluationScores();
    }

    private function backfillCompatibilityValues(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'saved_address') && Schema::hasColumn('students', 'address')) {
            DB::table('students')->whereNull('address')->whereNotNull('saved_address')->update(['address' => DB::raw('saved_address')]);
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'landmark') && Schema::hasColumn('students', 'address_landmark')) {
            DB::table('students')->whereNull('address_landmark')->whereNotNull('landmark')->update(['address_landmark' => DB::raw('landmark')]);
        }

        if (Schema::hasTable('master_tariffs')) {
            $hasLegacyRateId = Schema::hasColumn('master_tariffs', 'rate_id');
            if ($hasLegacyRateId && Schema::hasColumn('master_tariffs', 'tariff_id')) {
                DB::table('master_tariffs')->whereNull('tariff_id')->update(['tariff_id' => DB::raw('rate_id')]);
            }
            if (Schema::hasColumn('master_tariffs', 'category_rate') && Schema::hasColumn('master_tariffs', 'base_rate')) {
                DB::table('master_tariffs')->whereNull('base_rate')->update(['base_rate' => DB::raw('category_rate')]);
            }
            if (Schema::hasColumn('master_tariffs', 'learning_method') && Schema::hasColumn('master_tariffs', 'method')) {
                DB::table('master_tariffs')->whereNull('method')->update(['method' => DB::raw('learning_method')]);
            }
            if (Schema::hasColumn('master_tariffs', 'teacher_level') && Schema::hasColumn('master_tariffs', 'grade_level')) {
                DB::table('master_tariffs')->whereNull('grade_level')->update(['grade_level' => DB::raw('teacher_level')]);
            }
        }

        if (Schema::hasTable('orders')) {
            if (Schema::hasColumn('orders', 'learning_method') && Schema::hasColumn('orders', 'method')) {
                DB::table('orders')->whereNull('method')->update(['method' => DB::raw('learning_method')]);
            }
            if (Schema::hasColumn('orders', 'session_package') && Schema::hasColumn('orders', 'session_package_count')) {
                DB::table('orders')->whereNull('session_package_count')->update(['session_package_count' => DB::raw('session_package')]);
            }
            if (Schema::hasColumn('orders', 'total_amount') && Schema::hasColumn('orders', 'personal_amount')) {
                DB::table('orders')->whereNull('personal_amount')->update(['personal_amount' => DB::raw('total_amount')]);
            }
        }

        if (Schema::hasTable('teacher_availability_slots') && Schema::hasColumn('teacher_availability_slots', 'is_active') && Schema::hasColumn('teacher_availability_slots', 'is_available')) {
            DB::table('teacher_availability_slots')->update(['is_available' => DB::raw('is_active')]);
        }
    }

    private function addColumns(string $tableName, array $columns): void
    {
        if (!Schema::hasTable($tableName)) {
            return;
        }

        foreach ($columns as $column => $definition) {
            if (!Schema::hasColumn($tableName, $column)) {
                Schema::table($tableName, $definition);
            }
        }
    }

    private function createParentsAndDocuments(): void
    {
        if (!Schema::hasTable('parents')) {
            Schema::create('parents', function (Blueprint $table) {
                $table->uuid('parent_id')->primary();
                $table->uuid('user_id')->unique();
                $table->string('pin_hash');
                $table->string('phone_number', 20)->nullable();
                $table->timestamp('created_at')->nullable();
                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->uuid('document_id')->primary();
                $table->uuid('uploaded_by');
                $table->string('document_name', 150);
                $table->string('file_url');
                $table->string('category', 50);
                $table->timestamp('created_at')->nullable();
                $table->foreign('uploaded_by')->references('user_id')->on('users')->cascadeOnDelete();
            });
        }
    }

    private function createMissingTables(): void
    {
        if (!Schema::hasTable('evaluation_scores')) {
            Schema::create('evaluation_scores', function (Blueprint $table) {
                $table->uuid('evaluation_id')->primary();
                $table->uuid('session_id');
                $table->uuid('student_id');
                $table->unsignedInteger('pretest_score')->nullable();
                $table->unsignedInteger('posttest_score')->nullable();
                $table->decimal('progress_percentage', 5, 2)->nullable();
                $table->text('teacher_notes')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('teacher_level_requests')) {
            Schema::create('teacher_level_requests', function (Blueprint $table) {
                $table->uuid('request_id')->primary();
                $table->uuid('teacher_id');
                $table->string('requested_level', 50);
                $table->uuid('supporting_document_id')->nullable();
                $table->string('request_status', 30)->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->uuid('reviewed_by_admin_id')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->foreign('teacher_id')->references('teacher_id')->on('teachers')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('class_members')) {
            Schema::create('class_members', function (Blueprint $table) {
                $table->uuid('member_id')->primary();
                $table->uuid('squad_id');
                $table->uuid('package_id');
                $table->uuid('student_id');
                $table->string('membership_role', 20)->default('member');
                $table->timestamp('joined_at')->nullable();
                $table->string('membership_status', 20)->default('active');
                $table->unique(['squad_id', 'student_id']);
                $table->foreign('squad_id')->references('squad_id')->on('squads')->cascadeOnDelete();
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
                $table->foreign('student_id')->references('student_id')->on('students')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('tariff_audit_logs')) {
            Schema::create('tariff_audit_logs', function (Blueprint $table) {
                $table->uuid('audit_id')->primary();
                $table->uuid('tariff_id');
                $table->uuid('admin_id');
                $table->decimal('old_price', 12, 2);
                $table->decimal('new_price', 12, 2);
                $table->text('justification_notes');
                $table->timestamp('changed_at')->nullable();
            });
        }

        if (!Schema::hasTable('teacher_work_logs')) {
            Schema::create('teacher_work_logs', function (Blueprint $table) {
                $table->uuid('log_id')->primary();
                $table->uuid('teacher_id');
                $table->uuid('session_id')->unique();
                $table->unsignedInteger('duration_minutes');
                $table->decimal('gross_session_amount', 12, 2);
                $table->decimal('teacher_earnings', 12, 2);
                $table->string('payroll_status', 20)->default('pending');
                $table->date('payout_scheduled_date')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('teacher_payouts')) {
            Schema::create('teacher_payouts', function (Blueprint $table) {
                $table->uuid('payout_id')->primary();
                $table->uuid('teacher_id');
                $table->decimal('amount', 12, 2);
                $table->string('bank_name', 50);
                $table->string('bank_account_number', 50);
                $table->string('bank_account_holder', 100);
                $table->string('disbursement_reference', 100)->nullable();
                $table->string('payout_status', 20)->default('pending');
                $table->timestamp('payout_date')->nullable();
            });
        }

        if (!Schema::hasTable('digital_reports')) {
            Schema::create('digital_reports', function (Blueprint $table) {
                $table->uuid('report_id')->primary();
                $table->uuid('student_id');
                $table->uuid('package_id');
                $table->uuid('teacher_id');
                $table->decimal('average_pretest_score', 5, 2)->nullable();
                $table->decimal('average_posttest_score', 5, 2)->nullable();
                $table->decimal('progress_percentage', 5, 2)->nullable();
                $table->decimal('attendance_percentage', 5, 2)->nullable();
                $table->text('teacher_notes')->nullable();
                $table->string('report_status', 20)->default('draft');
                $table->timestamp('generated_at')->nullable();
            });
        }
    }

    private function migrateLegacyEvaluationScores(): void
    {
        if (!Schema::hasTable('nilai_evaluasi') || !Schema::hasTable('evaluation_scores')) {
            return;
        }

        foreach (DB::table('nilai_evaluasi')->orderBy('submitted_at')->get() as $legacyScore) {
            $evaluation = DB::table('evaluation_scores')
                ->where('session_id', $legacyScore->learning_session_id)
                ->where('student_id', $legacyScore->student_id)
                ->first();

            $values = [
                $legacyScore->exam_type === 'pre_test' ? 'pretest_score' : 'posttest_score' => (int) $legacyScore->score,
            ];

            if ($evaluation) {
                DB::table('evaluation_scores')->where('evaluation_id', $evaluation->evaluation_id)->update($values);
            } else {
                DB::table('evaluation_scores')->insert(array_merge([
                    'evaluation_id' => (string) Str::uuid(),
                    'session_id' => $legacyScore->learning_session_id,
                    'student_id' => $legacyScore->student_id,
                    'created_at' => $legacyScore->submitted_at,
                ], $values));
            }
        }
    }

    public function down(): void
    {
        // Compatibility migration intentionally retains schema/data on rollback.
    }
};
