<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_level_requests') && ! Schema::hasColumn('teacher_level_requests', 'supporting_document_path')) {
            Schema::table('teacher_level_requests', function (Blueprint $table) {
                $table->string('supporting_document_path', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_level_requests') && Schema::hasColumn('teacher_level_requests', 'supporting_document_path')) {
            Schema::table('teacher_level_requests', function (Blueprint $table) {
                $table->dropColumn('supporting_document_path');
            });
        }
    }
};
