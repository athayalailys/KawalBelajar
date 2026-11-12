<?php

namespace Tests\Feature\Classroom;

use App\Models\LearningSession;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_submit_pre_test_and_get_score(): void
    {
        /** @var \App\Models\User|\Illuminate\Contracts\Auth\Authenticatable $user */
        $user = User::factory()->create();
        $student = Student::create([
            'user_id' => $user->user_id,
            'education_level' => 'SMP',
        ]);

        $teacherUser = User::factory()->create();
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Ujian',
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
            'scheduled_at' => now()->subMinute(),
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ]);

        $question = QuestionBank::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'exam_type' => 'pre_test',
            'category' => 'umum',
            'question_text' => 'Soal 1',
            'answer_key' => 'A',
        ]);

        $session->update(['scheduled_at' => now()->addHour()]);
        $this->actingAs($user)
            ->getJson("/api/classroom/session/{$session->session_id}/exam/pre_test")
            ->assertForbidden();
        $this->actingAs($user)
            ->postJson("/api/classroom/session/{$session->session_id}/submit-exam", [
                'exam_type' => 'pre_test',
                'answers' => [['question_id' => $question->question_id, 'answer' => 'A']],
            ])
            ->assertForbidden();

        $session->update(['scheduled_at' => now()->subMinute()]);

        $response = $this->actingAs($user)
            ->postJson("/api/classroom/session/{$session->session_id}/submit-exam", [
                'exam_type' => 'pre_test',
                'answers' => [
                    ['question_id' => $question->question_id, 'answer' => 'A'],
                ]
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.score', 100);

        $sessionId = $session->session_id;
        $studentId = $student->student_id;
        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'pretest_score' => 100,
        ]);
        $this->assertDatabaseHas('student_answers', [
            'session_id' => $sessionId,
            'student_id' => $studentId,
            'question_id' => $question->question_id,
            'is_correct' => true,
        ]);

        $postTestQuestion = QuestionBank::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'exam_type' => 'post_test',
            'category' => 'umum',
            'question_text' => 'Soal tes akhir',
            'answer_key' => 'A',
        ]);

        $this->actingAs($user)
            ->getJson("/api/classroom/session/{$sessionId}/exam/post_test")
            ->assertForbidden();

        $session->update(['status' => 'completed', 'session_status' => 'completed']);
        $this->actingAs($user)
            ->getJson("/api/classroom/session/{$sessionId}/exam/post_test")
            ->assertOk()
            ->assertJsonPath('data.questions.0.question_id', $postTestQuestion->question_id);

        $this->postJson("/api/classroom/session/{$sessionId}/submit-exam", [
            'exam_type' => 'post_test',
            'answers' => [['question_id' => $postTestQuestion->question_id, 'answer' => 'A']],
        ])->assertOk()
            ->assertJsonPath('data.posttest_score', 100)
            ->assertJsonPath('data.progress_percentage', 0);
    }

    public function test_student_can_load_only_their_exam_questions_without_answer_keys(): void
    {
        $user = User::factory()->create();
        $student = Student::create([
            'user_id' => $user->user_id,
            'education_level' => 'SMP',
        ]);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Soal Privat',
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
            'scheduled_at' => now()->subMinute(),
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ]);
        $question = QuestionBank::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'exam_type' => 'pre_test',
            'category' => 'umum',
            'question_text' => 'Pilih jawaban benar',
            'option_a' => 'Pilihan A',
            'answer_key' => 'A',
        ]);

        $this->actingAs($user)
            ->getJson("/api/classroom/session/{$session->session_id}/exam/pre_test")
            ->assertOk()
            ->assertJsonPath('data.already_submitted', false)
            ->assertJsonPath('data.questions.0.question_id', $question->question_id)
            ->assertJsonPath('data.questions.0.option_a', 'Pilihan A')
            ->assertJsonMissingPath('data.questions.0.answer_key');

        $otherUser = User::factory()->create();
        Student::create(['user_id' => $otherUser->user_id, 'education_level' => 'SMP']);

        $this->actingAs($otherUser)
            ->getJson("/api/classroom/session/{$session->session_id}/exam/pre_test")
            ->assertForbidden();
    }
}
