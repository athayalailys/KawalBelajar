<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_package_availability_slots')) {
            Schema::create('module_package_availability_slots', function (Blueprint $table) {
                $table->uuid('package_id');
                $table->uuid('slot_id');
                $table->primary(['package_id', 'slot_id']);
                $table->foreign('package_id')->references('package_id')->on('module_packages')->cascadeOnDelete();
                $table->foreign('slot_id')->references('slot_id')->on('teacher_availability_slots')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('module_package_availability_slots');
    }
};
