<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('module_packages')) {
            return;
        }

        Schema::table('module_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('module_packages', 'zoom_meeting_url')) {
                $table->string('zoom_meeting_url', 255)->nullable();
            }
            if (! Schema::hasColumn('module_packages', 'home_visit_area')) {
                $table->text('home_visit_area')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('module_packages')) {
            return;
        }

        Schema::table('module_packages', function (Blueprint $table) {
            if (Schema::hasColumn('module_packages', 'zoom_meeting_url')) {
                $table->dropColumn('zoom_meeting_url');
            }
            if (Schema::hasColumn('module_packages', 'home_visit_area')) {
                $table->dropColumn('home_visit_area');
            }
        });
    }
};
