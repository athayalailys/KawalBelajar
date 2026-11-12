<?php

namespace Tests\Feature\Classroom;

use App\Models\EvaluationScore;
use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoomGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_without_pretest_is_denied_room_access(): void
    {
        [$studentUser, $session] = $this->createSession();

        $this->actingAs($studentUser)
            ->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertForbidden()
            ->assertJsonPath('data.is_gated', true);
    }

    public function test_student_with_pretest_can_access_zoom_url(): void
    {
        [$studentUser, $session, $student] = $this->createSession();
        EvaluationScore::create([
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 75,
        ]);

        $this->actingAs($studentUser)
            ->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertOk()
            ->assertJsonPath('data.session_id', $session->session_id)
            ->assertJsonPath('data.zoom_meeting_url', $session->zoom_meeting_url)
            ->assertJsonPath('data.is_gated', false);
    }

    public function test_student_cannot_access_zoom_before_session_start_even_after_pretest(): void
    {
        [$studentUser, $session, $student] = $this->createSession();
        $session->update(['scheduled_at' => now()->addHour()]);
        EvaluationScore::create([
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 75,
        ]);

        $this->actingAs($studentUser)
            ->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertForbidden()
            ->assertJsonPath('data.gate_reason', 'not_started');
    }

    public function test_tutor_can_access_zoom_url_without_pretest(): void
    {
        [, $session, , $tutorUser] = $this->createSession();

        $this->actingAs($tutorUser)
            ->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertOk()
            ->assertJsonPath('data.zoom_meeting_url', $session->zoom_meeting_url)
            ->assertJsonPath('data.is_gated', false);
    }

    private function createSession(): array
    {
        $tutorUser = User::factory()->create(['role' => 'tutor']);
        $teacher = Teacher::create([
            'user_id' => $tutorUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);
        $studentUser = User::factory()->create(['role' => 'student']);
        $student = Student::create([
            'user_id' => $studentUser->user_id,
            'education_level' => 'SMP',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Zoom',
            'grade_level' => 'SMP',
            'base_price' => 100000,
            'is_active' => true,
        ]);
        $order = Order::create([
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online',
            'session_package' => 1,
            'total_amount' => 100000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'learning_method' => 'online',
            'scheduled_at' => now()->subMinute(),
            'status' => 'scheduled',
            'session_status' => 'scheduled',
            'zoom_meeting_url' => 'https://zoom.us/j/123456789',
        ]);

        return [$studentUser, $session, $student, $tutorUser];
    }
}
