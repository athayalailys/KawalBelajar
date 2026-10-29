<?php

namespace Tests\Feature\Classroom;

use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_can_get_navigation_route_for_home_visit(): void
    {
        [$tutorUser, $session] = $this->createTutorSession([
            'latitude' => -3.316694,
            'longitude' => 114.590111,
            'onsite_address' => 'Jl. Veteran No. 12',
            'address_landmark' => 'Samping Minimarket',
        ]);

        $response = $this->actingAs($tutorUser)
            ->getJson("/api/classroom/session/{$session->session_id}/navigation");

        $response->assertStatus(200)
            ->assertJsonPath('data.address', 'Jl. Veteran No. 12')
            ->assertJsonPath('data.google_maps_url', 'https://www.google.com/maps/dir/?api=1&destination=-3.316694,114.590111');
    }

    public function test_tutor_can_clock_in_successfully_and_status_becomes_in_progress(): void
    {
        [$tutorUser, $session] = $this->createTutorSession([
            'status' => 'scheduled',
            'session_status' => 'scheduled',
            'absensi_masuk_guru' => null,
            'teacher_clock_in' => null,
        ]);

        $response = $this->actingAs($tutorUser)
            ->postJson('/api/classroom/attendance/clock-in', [
                'learning_session_id' => $session->session_id,
                'session_id' => $session->session_id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.status_label', 'Dalam Pembelajaran');

        $this->assertDatabaseHas('learning_sessions', [
            'session_id' => $session->session_id,
            'status' => 'in_progress',
            'session_status' => 'in_progress',
        ]);
    }

    public function test_tutor_cannot_clock_in_twice(): void
    {
        [$tutorUser, $session] = $this->createTutorSession([
            'status' => 'in_progress',
            'absensi_masuk_guru' => now(),
            'teacher_clock_in' => now(),
        ]);

        $response = $this->actingAs($tutorUser)
            ->postJson('/api/classroom/attendance/clock-in', [
                'learning_session_id' => $session->session_id,
                'session_id' => $session->session_id,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Tutor sudah melakukan Clock-In pada sesi ini.');
    }

    private function createTutorSession(array $sessionAttributes = []): array
    {
        $tutorUser = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $tutorUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);

        $studentUser = User::factory()->create();
        $student = Student::create([
            'user_id' => $studentUser->user_id,
            'education_level' => 'SMP',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Uji Classroom',
            'grade_level' => 'SMP',
            'base_price' => 100000,
            'is_active' => true,
        ]);
        $order = Order::create([
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'home_visit',
            'session_package' => 1,
            'total_amount' => 100000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create(array_merge([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'learning_method' => 'home_visit',
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ], $sessionAttributes));

        return [$tutorUser, $session];
    }
}
