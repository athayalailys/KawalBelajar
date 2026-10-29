<?php

namespace Tests\Feature;

use App\Models\LearningSession;
use App\Models\EvaluationScore;
use App\Models\LearningMaterial;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\QuestionBank;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\TutorPackageRate;
use App\Models\User;
use Database\Seeders\DashboardE2ESeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentDashboardE2ETest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DashboardE2ESeeder::class);
    }

    public function test_student_without_sessions_sees_empty_dashboard_state(): void
    {
        $studentB = User::query()->where('email', 'siswaB@kawalbelajar.id')->firstOrFail();

        $response = $this->actingAs($studentB)->getJson('/api/student/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.has_active_session', false)
            ->assertJsonPath('data.agenda', []);
    }

    public function test_student_dashboard_shows_active_session_locked_until_pretest(): void
    {
        $studentA = User::query()->where('email', 'siswaA@kawalbelajar.id')->firstOrFail();

        $response = $this->actingAs($studentA)->getJson('/api/student/dashboard');

        $response->assertOk()
            ->assertJsonPath('data.has_active_session', true)
            ->assertJsonPath('data.upcoming_session.is_locked', true);
        $this->assertNull($response->json('data.upcoming_session.zoom_meeting_url'));
    }

    public function test_pretest_submission_unlocks_session_room(): void
    {
        $studentA = User::query()->where('email', 'siswaA@kawalbelajar.id')->firstOrFail();
        $student = Student::query()->where('user_id', $studentA->user_id)->firstOrFail();
        $session = LearningSession::query()->where('student_id', $student->student_id)->firstOrFail();
        $question = QuestionBank::query()->where('exam_type', 'pre_test')->firstOrFail();

        $this->actingAs($studentA)
            ->postJson("/api/classroom/session/{$session->session_id}/submit-exam", [
                'exam_type' => 'pre_test',
                'answers' => [['question_id' => $question->question_id, 'answer' => '4']],
            ])
            ->assertOk()
            ->assertJsonPath('data.exam_type', 'pre_test')
            ->assertJsonPath('data.score', 100);

        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 100,
        ]);

        $this->getJson("/api/classroom/session/{$session->session_id}/room-access")
            ->assertOk()
            ->assertJsonPath('data.is_locked', false)
            ->assertJsonPath('data.zoom_meeting_url', 'https://meet.example.test/e2e-session');
    }

    public function test_student_cannot_access_another_students_session(): void
    {
        $studentB = User::query()->where('email', 'siswaB@kawalbelajar.id')->firstOrFail();
        $studentA = User::query()->where('email', 'siswaA@kawalbelajar.id')->firstOrFail();
        $student = Student::query()->where('user_id', $studentA->user_id)->firstOrFail();
        $session = LearningSession::query()->where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($studentB)
            ->getJson("/api/classroom/session/{$session->session_id}/room-access")
            ->assertForbidden();
    }

    public function test_tutor_can_clock_in_and_clock_out_own_session(): void
    {
        $tutor = User::query()->where('email', 'tutor@kawalbelajar.id')->firstOrFail();
        $studentA = User::query()->where('email', 'siswaA@kawalbelajar.id')->firstOrFail();
        $student = Student::query()->where('user_id', $studentA->user_id)->firstOrFail();
        $session = LearningSession::query()->where('student_id', $student->student_id)->firstOrFail();

        $this->actingAs($tutor)
            ->postJson('/api/classroom/attendance/clock-in', ['session_id' => $session->session_id])
            ->assertOk();

        $this->assertNotNull($session->fresh()->absensi_masuk_guru);
        $this->assertNotNull($session->fresh()->teacher_clock_in);

        $this->postJson('/api/classroom/attendance/clock-out', ['session_id' => $session->session_id])
            ->assertOk();

        $session->refresh();
        $this->assertNotNull($session->absensi_keluar_guru);
        $this->assertNotNull($session->teacher_clock_out);
        $this->assertSame('completed', $session->status);
        $this->assertSame('completed', $session->session_status);
    }

    public function test_student_learning_lifecycle_from_registration_to_parent_progress(): void
    {
        config(['services.xendit.secret_key' => 'test-secret', 'services.xendit.webhook_token' => 'e2e-callback-token']);
        Http::fake(['https://api.xendit.co/v2/invoices' => Http::response([
            'id' => 'invoice-lifecycle',
            'invoice_url' => 'https://pay.example.test/lifecycle',
        ], 200)]);

        $registration = $this->postJson('/api/student/register', [
            'full_name' => 'Siswa Lifecycle',
            'email' => 'lifecycle@kawalbelajar.id',
            'phone_number' => '08123456789',
            'password' => 'password123',
            'education_level' => 'SMP',
            'parent_pin' => '123456',
        ])->assertCreated();
        $studentUser = User::findOrFail($registration->json('data.user_id'));
        $student = Student::where('user_id', $studentUser->user_id)->firstOrFail();
        $this->assertTrue(Hash::check('123456', $student->parent_pin_hash));

        $this->post('/login', ['email' => $studentUser->email, 'password' => 'password123'])
            ->assertRedirect(route('student.dashboard'));

        /** @var User $teacherUser */
        $teacherUser = User::factory(1)->create(['role' => 'teacher'])->firstOrFail();
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
            'approved_levels' => ['SMP'],
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Paket Lifecycle',
            'grade_level' => 'SMP',
            'base_price' => 50000,
            'is_active' => true,
        ]);
        LearningMaterial::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'title' => 'Materi Siklus',
            'material_type' => 'pdf',
            'file_or_url' => 'materials/lifecycle.pdf',
        ]);
        TutorPackageRate::create([
            'package_id' => $package->package_id,
            'teacher_level' => 'junior',
            'learning_method' => 'online_zoom',
            'category_rate' => 50000,
        ]);
        $slot = TeacherAvailabilitySlot::create([
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => now()->addDay()->format('l'),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'learning_method' => 'online_zoom',
            'is_active' => true,
        ]);

        $checkout = $this->actingAs($studentUser)->postJson('/api/student/booking/checkout', [
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'format' => 'solo',
            'selected_slot_id' => $slot->slot_id,
        ])->assertCreated();
        $order = Order::findOrFail($checkout->json('data.order_id'));

        $this->postJson('/api/payments/xendit/webhook', [
            'id' => 'invoice-lifecycle',
            'external_id' => $order->invoice_number,
            'status' => 'SETTLED',
            'amount' => $order->total_amount,
        ], ['x-callback-token' => 'e2e-callback-token'])->assertOk();

        $session = LearningSession::where('order_id', $order->order_id)->firstOrFail();
        $session->update(['zoom_meeting_url' => 'https://zoom.us/j/lifecycle']);
        $this->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertForbidden()
            ->assertJsonPath('data.is_gated', true);

        $pretest = QuestionBank::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'exam_type' => 'pre_test',
            'category' => 'E2E',
            'question_text' => 'Hasil 2 + 2?',
            'answer_key' => '4',
        ]);
        $posttest = QuestionBank::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'exam_type' => 'post_test',
            'category' => 'E2E',
            'question_text' => 'Hasil 3 + 1?',
            'answer_key' => '4',
        ]);

        $this->postJson("/api/classroom/session/{$session->session_id}/submit-exam", [
            'exam_type' => 'pre_test',
            'answers' => [['question_id' => $pretest->question_id, 'answer' => '0']],
        ])->assertOk()->assertJsonPath('data.score', 0);
        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 0,
        ]);
        $this->getJson("/api/classroom/session/{$session->session_id}/room")
            ->assertOk()->assertJsonPath('data.zoom_meeting_url', 'https://zoom.us/j/lifecycle');

        $this->postJson("/api/classroom/session/{$session->session_id}/submit-exam", [
            'exam_type' => 'post_test',
            'answers' => [['question_id' => $posttest->question_id, 'answer' => '4']],
        ])->assertOk()->assertJsonPath('data.score', 100);

        $this->actingAs($teacherUser)->postJson('/api/classroom/attendance/clock-in', ['session_id' => $session->session_id])->assertOk();
        $this->postJson('/api/classroom/attendance/clock-out', ['session_id' => $session->session_id])->assertOk();
        $this->postJson("/api/classroom/session/{$session->session_id}/evaluation", [
            'attendances' => [['student_id' => $student->student_id, 'status' => 'present']],
            'posttest_score' => 100,
            'teacher_notes' => 'Kemajuan baik.',
        ])->assertOk();

        $this->assertDatabaseHas('learning_sessions', ['session_id' => $session->session_id, 'status' => 'completed']);
        $this->assertDatabaseHas('student_attendances', ['session_id' => $session->session_id, 'student_id' => $student->student_id, 'status' => 'present']);
        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'posttest_score' => 100,
            'progress_percentage' => 100,
        ]);

        $this->actingAs($studentUser)->postJson('/api/student/parent-mode/verify-pin', ['pin' => '123456'])
            ->assertOk()
            ->assertJsonPath('data.posttest_score', 100)
            ->assertJsonPath('data.attendance_count', 1);
    }
}
