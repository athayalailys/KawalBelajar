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

class StudentEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_can_submit_attendance_and_posttest_evaluation(): void
    {
        [$tutorUser, $session, $student] = $this->createTutorSession();
        EvaluationScore::create([
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 70,
        ]);

        $this->actingAs($tutorUser)
            ->postJson("/api/classroom/session/{$session->session_id}/evaluation", [
                'attendances' => [[
                    'student_id' => $student->student_id,
                    'status' => 'present',
                ]],
                'posttest_score' => 85,
                'teacher_notes' => 'Pemahaman siswa meningkat.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.attendance_count', 1)
            ->assertJsonPath('data.evaluations.0.posttest_score', 85);

        $this->assertDatabaseHas('student_attendances', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'status' => 'present',
        ]);
        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'posttest_score' => 85,
            'teacher_notes' => 'Pemahaman siswa meningkat.',
            'progress_percentage' => 15,
        ]);
    }

    public function test_invalid_attendance_status_is_rejected(): void
    {
        [$tutorUser, $session, $student] = $this->createTutorSession();

        $this->actingAs($tutorUser)
            ->postJson("/api/classroom/session/{$session->session_id}/evaluation", [
                'attendances' => [[
                    'student_id' => $student->student_id,
                    'status' => 'late',
                ]],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('attendances.0.status');
    }

    private function createTutorSession(): array
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
            'package_name' => 'Paket Evaluasi',
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
        $session = LearningSession::create([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'learning_method' => 'home_visit',
            'status' => 'completed',
            'session_status' => 'completed',
        ]);

        return [$tutorUser, $session, $student];
    }
}
