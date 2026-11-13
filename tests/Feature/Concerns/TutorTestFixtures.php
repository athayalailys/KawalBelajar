<?php

namespace Tests\Feature\Concerns;

use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\User;

trait TutorTestFixtures
{
    protected function makeTutor(array $userAttributes = [], array $teacherAttributes = []): array
    {
        $user = User::factory()->create(array_merge([
            'role' => 'teacher',
            'is_active' => true,
        ], $userAttributes));

        $teacher = Teacher::create(array_merge([
            'user_id' => $user->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'Junior',
            'approved_levels' => ['SD', 'SMP', 'SMA', 'UMUM'],
            'verified_levels' => ['SD', 'SMP', 'SMA', 'UMUM'],
            'requested_levels' => ['SMP'],
            'wallet_balance' => 0,
        ], $teacherAttributes));

        return [$user, $teacher];
    }

    protected function makeStudent(array $userAttributes = [], array $studentAttributes = []): array
    {
        $user = User::factory()->create(array_merge(['role' => 'student'], $userAttributes));
        $student = Student::create(array_merge([
            'user_id' => $user->user_id,
            'education_level' => 'SMP',
        ], $studentAttributes));

        return [$user, $student];
    }

    protected function makeTutorSlot(Teacher $teacher, array $attributes = []): TeacherAvailabilitySlot
    {
        return TeacherAvailabilitySlot::create(array_merge([
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => 'Friday',
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'learning_method' => null,
            'is_active' => true,
            'is_available' => true,
        ], $attributes));
    }

    protected function makeTutorPackage(Teacher $teacher, array $attributes = []): ModulePackage
    {
        return ModulePackage::create(array_merge([
            'teacher_id' => $teacher->teacher_id,
            'package_name' => 'Program Uji Tutor',
            'grade_level' => 'SMP',
            'subject_name' => 'Matematika',
            'learning_method' => 'online_zoom',
            'max_capacity' => 20,
            'description' => 'Program untuk pengujian fitur tutor.',
            'target_learning' => 'Memahami materi dengan baik.',
            'supported_session_packages' => '1',
            'base_price' => 75000,
            'is_active' => true,
            'is_published' => false,
            'is_open' => false,
        ], $attributes));
    }

    protected function makeTutorSession(Teacher $teacher, array $attributes = []): array
    {
        [, $student] = $this->makeStudent();
        $package = $this->makeTutorPackage($teacher);
        $order = Order::create([
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'home_visit',
            'session_package' => 1,
            'session_package_count' => 1,
            'selected_slot_id' => null,
            'total_amount' => 300000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create(array_merge([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'scheduled_at' => now()->subMinute(),
            'duration_minutes' => 60,
            'learning_method' => 'home_visit',
            'status' => 'scheduled',
            'session_status' => 'scheduled',
            'onsite_address' => 'Jl. Uji No. 10',
            'address_landmark' => 'Dekat taman kota',
            'latitude' => -3.316694,
            'longitude' => 114.590111,
        ], $attributes));

        return [$student, $package, $order, $session];
    }
}
