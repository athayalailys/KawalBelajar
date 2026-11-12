<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentClassroomPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_classroom_menu_opens_the_student_session_view(): void
    {
        /** @var User&\Illuminate\Contracts\Auth\Authenticatable $studentUser */
        $studentUser = User::factory()->create();
        $student = Student::create(['user_id' => $studentUser->user_id, 'education_level' => 'SMP']);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Ruang Kelas',
            'grade_level' => 'SMP',
            'base_price' => 100000,
            'is_active' => true,
        ]);
        $order = Order::create([
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'total_amount' => 100000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'learning_method' => 'online_zoom',
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ]);

        $this->actingAs($studentUser)->withoutVite()
            ->get(route('student.classroom'))
            ->assertOk()
            ->assertSee('Paket Ruang Kelas')
            ->assertSee($session->session_id)
            ->assertSee('Tes awal belum dikerjakan');
    }

    public function test_student_cannot_select_another_students_session_page(): void
    {
        /** @var User&\Illuminate\Contracts\Auth\Authenticatable $ownerUser */
        $ownerUser = User::factory()->create();
        $owner = Student::create(['user_id' => $ownerUser->user_id, 'education_level' => 'SMP']);
        /** @var User&\Illuminate\Contracts\Auth\Authenticatable $studentUser */
        $studentUser = User::factory()->create();
        Student::create(['user_id' => $studentUser->user_id, 'education_level' => 'SMP']);
        /** @var User&\Illuminate\Contracts\Auth\Authenticatable $teacherUser */
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Privat',
            'grade_level' => 'SMP',
            'base_price' => 100000,
            'is_active' => true,
        ]);
        $order = Order::create([
            'student_id' => $owner->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'total_amount' => 100000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create([
            'order_id' => $order->order_id,
            'student_id' => $owner->student_id,
            'teacher_id' => $teacher->teacher_id,
            'learning_method' => 'online_zoom',
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ]);

        $this->actingAs($studentUser)->withoutVite()
            ->get(route('student.classroom', ['session_id' => $session->session_id]))
            ->assertNotFound();
    }
}
