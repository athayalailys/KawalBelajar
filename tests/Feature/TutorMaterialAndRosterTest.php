<?php

namespace Tests\Feature;

use App\Models\EvaluationScore;
use App\Models\LearningMaterial;
use App\Models\LearningSession;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\QuestionBank;
use App\Models\StudentAttendance;
use App\Models\TutorClassSessionContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class TutorMaterialAndRosterTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    public function test_mat_001_uploads_material_for_a_draft_class(): void
    {
        Storage::fake('public');
        [$user, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher);

        $this->actingAs($user)->post(route('tutor.classes.materials.session.save', [$package->package_id, 1]), [
            'topic' => 'Persamaan Linear',
            'learning_target' => 'Pembelajar mampu menyelesaikan persamaan linear.',
            'module_file' => UploadedFile::fake()->create('modul.pdf', 200, 'application/pdf'),
        ])->assertRedirect(route('tutor.classes.materials', ['packageId' => $package->package_id, 'session' => 1]));

        $material = LearningMaterial::where('package_id', $package->package_id)->firstOrFail();
        $this->assertSame(1, (int) $material->session_number);
        Storage::disk('public')->assertExists($material->file_or_url);
    }

    public function test_quiz_001_saves_multiple_choice_questions_with_one_answer_key(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher);
        $question = $this->questionPayload();

        $this->actingAs($user)->post(route('tutor.classes.materials.session.save', [$package->package_id, 1]), [
            'topic' => 'Persamaan Linear',
            'learning_target' => 'Menyelesaikan persamaan linear.',
            'questions' => [$question],
        ])->assertRedirect();

        $this->assertDatabaseHas('question_banks', [
            'package_id' => $package->package_id,
            'exam_type' => 'pre_test',
            'question_text' => $question['question_text'],
            'answer_key' => 'b',
        ]);
        $this->assertDatabaseCount('question_banks', 2);
    }

    public function test_pub_001_publication_is_blocked_until_session_content_material_and_questions_exist(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher);

        $this->actingAs($user)->post(route('tutor.classes.materials.publish', $package->package_id))
            ->assertRedirect()
            ->assertSessionHasErrors('publication');

        $this->assertFalse($package->fresh()->is_published);
    }

    public function test_pub_002_ready_class_is_published_and_appears_in_student_catalog(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher, ['is_published' => false, 'is_open' => false]);
        $this->addRequiredSessionContent($teacher, $package);

        $this->actingAs($user)->post(route('tutor.classes.materials.publish', $package->package_id))
            ->assertRedirect(route('tutor.classes'));

        $this->assertDatabaseHas('module_packages', [
            'package_id' => $package->package_id,
            'is_published' => true,
            'is_open' => true,
        ]);
        $this->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()
            ->assertJsonFragment(['package_name' => $package->package_name]);
    }

    public function test_roster_001_closing_enrollment_removes_class_from_catalog(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher, ['is_published' => true, 'is_open' => true]);
        $this->addRequiredSessionContent($teacher, $package);

        $this->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonFragment(['package_name' => $package->package_name]);

        $this->actingAs($user)->patch(route('tutor.classes.listing', $package->package_id), ['is_open' => false])
            ->assertRedirect();

        $this->assertFalse($package->fresh()->is_open);
        $this->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonMissing(['package_name' => $package->package_name]);
    }

    public function test_roster_002_displays_student_attendance_and_pre_post_scores(): void
    {
        [$user, $teacher] = $this->makeTutor();
        [$student, $package, $order, $session] = $this->makeTutorSessionForPackage($teacher);
        EvaluationScore::create([
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'pretest_score' => 40,
            'posttest_score' => 80,
            'progress_percentage' => 100,
        ]);
        StudentAttendance::create([
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'attendance_time' => now(),
            'status' => 'present',
        ]);

        $this->actingAs($user)->get(route('tutor.classes.roster', $package->package_id))
            ->assertOk()
            ->assertSee($student->user->full_name)
            ->assertSee('40')
            ->assertSee('80')
            ->assertSee('Hadir');
    }

    public function test_link_001_tutor_can_manage_a_valid_meeting_link_for_a_session(): void
    {
        $this->assertTrue(
            Route::has('tutor.sessions.meeting-link'),
            'Belum ada endpoint Tutor untuk menyimpan dan memvalidasi tautan Zoom/Google Meet.'
        );
    }

    private function questionPayload(): array
    {
        return [
            'question_text' => 'Berapakah hasil 2 + 2?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'answer_key' => 'b',
        ];
    }

    private function addRequiredSessionContent($teacher, ModulePackage $package): void
    {
        $this->makeTutorSlot($teacher);
        MasterTariff::create([
            'rate_id' => (string) Str::uuid(),
            'package_id' => $package->package_id,
            'teacher_level' => $teacher->teacher_level,
            'learning_method' => $package->learning_method,
            'category_rate' => 75000,
        ]);
        TutorClassSessionContent::create([
            'package_id' => $package->package_id,
            'session_number' => 1,
            'topic' => 'Persamaan Linear',
            'learning_target' => 'Memahami persamaan linear.',
        ]);
        LearningMaterial::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'session_number' => 1,
            'title' => 'Modul Persamaan Linear',
            'material_type' => 'pdf',
            'file_or_url' => 'https://example.test/modul.pdf',
            'created_at' => now(),
        ]);
        foreach (range(1, 3) as $number) {
            QuestionBank::create([
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'session_number' => 1,
                'exam_type' => 'pre_test',
                'category' => 'Persamaan',
                'question_text' => "Soal {$number}",
                'option_a' => 'A',
                'option_b' => 'B',
                'option_c' => 'C',
                'option_d' => 'D',
                'answer_key' => 'a',
            ]);
        }
    }

    private function makeTutorSessionForPackage($teacher): array
    {
        [$userStudent, $student] = $this->makeStudent();
        $package = $this->makeTutorPackage($teacher);
        $order = Order::create([
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'home_visit',
            'session_package' => 1,
            'session_package_count' => 1,
            'total_amount' => 300000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);
        $session = LearningSession::create([
            'order_id' => $order->order_id,
            'student_id' => $student->student_id,
            'teacher_id' => $teacher->teacher_id,
            'scheduled_at' => now()->subMinute(),
            'duration_minutes' => 60,
            'learning_method' => 'home_visit',
            'status' => 'scheduled',
            'session_status' => 'scheduled',
        ]);

        return [$student, $package, $order, $session];
    }
}
