<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_tariffs') && !Schema::hasColumn('master_tariffs', 'learning_method')) {
            Schema::table('master_tariffs', function (Blueprint $table) {
                $table->string('learning_method')->nullable();
            });
        }
    }

    public function down(): void
    {
    }
};
