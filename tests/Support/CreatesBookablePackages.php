<?php

namespace Tests\Support;

use App\Models\LearningMaterial;
use App\Models\ModulePackage;
use App\Models\QuestionBank;
use App\Models\Teacher;
use App\Models\TutorClassSessionContent;

trait CreatesBookablePackages
{
    protected function makePackageReadyForEnrollment(Teacher $teacher, ModulePackage $package): void
    {
        $package->update([
            'is_published' => true,
            'is_open' => true,
            'supported_session_packages' => '1,4',
        ]);

        foreach (range(1, $package->plannedSessionCount()) as $sessionNumber) {
            TutorClassSessionContent::updateOrCreate(
                ['package_id' => $package->package_id, 'session_number' => $sessionNumber],
                ['topic' => "Materi {$sessionNumber}", 'learning_target' => "Tujuan {$sessionNumber}"],
            );

            $material = LearningMaterial::query()
                ->where('package_id', $package->package_id)
                ->where('session_number', $sessionNumber)
                ->first();

            if ($material) {
                $material->update(['teacher_id' => $teacher->teacher_id]);
            } else {
                LearningMaterial::create([
                    'teacher_id' => $teacher->teacher_id,
                    'package_id' => $package->package_id,
                    'session_number' => $sessionNumber,
                    'title' => "Materi {$sessionNumber}",
                    'material_type' => 'pdf',
                    'file_or_url' => "materials/session-{$sessionNumber}.pdf",
                ]);
            }

            $questionCount = QuestionBank::query()
                ->where('package_id', $package->package_id)
                ->where('session_number', $sessionNumber)
                ->where('exam_type', 'pre_test')
                ->count();

            for ($index = $questionCount; $index < 3; $index++) {
                QuestionBank::create([
                    'teacher_id' => $teacher->teacher_id,
                    'package_id' => $package->package_id,
                    'session_number' => $sessionNumber,
                    'exam_type' => 'pre_test',
                    'category' => 'Test readiness',
                    'question_text' => "Pertanyaan {$sessionNumber}-{$index}",
                    'answer_key' => 'A',
                ]);
            }
        }
    }
}
