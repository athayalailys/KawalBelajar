<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Models\ModulePackage;
use App\Models\Teacher;
use App\Models\TutorPackageRate;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed data demo yang dibutuhkan untuk menelusuri alur pembelajar.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            DashboardE2ESeeder::class,
        ]);

        $teacher = Teacher::query()
            ->whereHas('user', fn ($query) => $query->where('email', 'tutor@kawalbelajar.id'))
            ->firstOrFail();
        $package = ModulePackage::query()
            ->where('package_name', 'Paket E2E Dashboard')
            ->firstOrFail();

        foreach (['home_visit', 'online_zoom'] as $method) {
            TutorPackageRate::query()->updateOrCreate(
                [
                    'package_id' => $package->package_id,
                    'teacher_level' => $teacher->teacher_level,
                    'learning_method' => $method,
                ],
                ['category_rate' => 100000],
            );
        }

        LearningMaterial::query()->updateOrCreate(
            [
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'title' => 'Modul Matematika Paket E2E',
            ],
            [
                'material_type' => 'pdf',
                'file_or_url' => null,
                'video_duration_minutes' => null,
            ],
        );
    }
}
