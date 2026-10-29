<?php

namespace Database\Seeders;

use App\Models\LearningSchedule;
use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DashboardE2ESeeder extends Seeder
{
    public function run(): void
    {
        $studentAUser = $this->user('siswaA@kawalbelajar.id', 'Siswa A', 'student');
        $studentBUser = $this->user('siswaB@kawalbelajar.id', 'Siswa B', 'student');
        $teacherUser = $this->user('tutor@kawalbelajar.id', 'Tutor E2E', 'teacher');

        $studentA = Student::query()->updateOrCreate(
            ['user_id' => $studentAUser->user_id],
            ['education_level' => 'SMP', 'saved_address' => 'Jl. Pengujian E2E No. 1', 'landmark' => 'Dekat taman', 'latitude' => -6.20000000, 'longitude' => 106.81666600],
        );
        Student::query()->updateOrCreate(
            ['user_id' => $studentBUser->user_id],
            ['education_level' => 'SMP'],
        );

        $teacher = Teacher::query()->updateOrCreate(
            ['user_id' => $teacherUser->user_id],
            [
                'university' => 'Universitas E2E',
                'focus_subject' => 'Matematika',
                'approved_levels' => ['SMP'],
                'cv_status' => 'approved',
                'teacher_level' => 'junior',
                'is_active' => true,
            ],
        );

        $package = ModulePackage::query()->firstOrCreate(
            ['package_name' => 'Paket E2E Dashboard'],
            ['grade_level' => 'SMP', 'description' => 'Paket khusus pengujian E2E', 'base_price' => 100000, 'is_active' => true],
        );

        $slot = TeacherAvailabilitySlot::query()->firstOrCreate(
            ['teacher_id' => $teacher->teacher_id, 'day_of_week' => 'Monday', 'start_time' => '10:00:00'],
            ['end_time' => '11:00:00', 'learning_method' => 'home_visit', 'is_active' => true],
        );

        $start = now()->addDays(3)->setTime(10, 0);
        $schedule = LearningSchedule::query()->firstOrCreate(
            ['teacher_id' => $teacher->teacher_id, 'package_id' => $package->package_id, 'method' => 'home_visit', 'start_time' => $start],
            ['location' => $studentA->saved_address, 'end_time' => $start->copy()->addHour()],
        );

        $order = Order::query()->firstOrCreate(
            ['invoice_number' => 'E2E-DASHBOARD-STUDENT-A'],
            [
                'student_id' => $studentA->student_id,
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'learning_method' => 'home_visit',
                'session_package' => 1,
                'selected_slot_id' => $slot->slot_id,
                'total_amount' => 100000,
                'payment_status' => 'settled',
                'order_status' => 'confirmed',
                'order_date' => now(),
            ],
        );
        $order->forceFill([
            'student_id' => $studentA->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'squad_id' => null,
            'learning_method' => 'home_visit',
            'session_package' => 1,
            'selected_slot_id' => $slot->slot_id,
            'total_amount' => 100000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ])->save();

        LearningSession::query()->firstOrCreate(
            ['order_id' => $order->order_id],
            [
                'student_id' => $studentA->student_id,
                'teacher_id' => $teacher->teacher_id,
                'schedule_id' => $schedule->schedule_id,
                'scheduled_at' => $start,
                'duration_minutes' => 60,
                'learning_method' => 'home_visit',
                'session_status' => 'scheduled',
                'status' => 'scheduled',
                'zoom_meeting_url' => 'https://meet.example.test/e2e-session',
                'onsite_address' => $studentA->saved_address,
                'student_address' => $studentA->saved_address,
                'latitude' => $studentA->latitude,
                'longitude' => $studentA->longitude,
                'address_landmark' => $studentA->landmark,
            ],
        );

        QuestionBank::query()->firstOrCreate(
            ['teacher_id' => $teacher->teacher_id, 'package_id' => $package->package_id, 'exam_type' => 'pre_test', 'category' => 'E2E'],
            ['question_text' => 'Hasil dari 2 + 2?', 'answer_key' => '4'],
        );
    }

    private function user(string $email, string $name, string $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'username' => Str::before($email, '@'),
                'full_name' => $name,
                'password_hash' => Hash::make('password'),
                'role' => $role,
                'is_active' => true,
            ],
        );
    }
}
