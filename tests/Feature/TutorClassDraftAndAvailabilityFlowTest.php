<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TutorClassDraft;
use App\Models\TutorClassSessionContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class TutorClassDraftAndAvailabilityFlowTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_tutor_class_wizard_opens_on_master_availability_and_saves_restorable_server_draft(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $slot = $this->makeTutorSlot($teacher);

        $this->actingAs($user)->get(route('tutor.classes.create'))
            ->assertOk()
            ->assertSee('Langkah 1: Kesediaan Jadwal')
            ->assertSee('data-wizard-step="1"', false)
            ->assertSee('kb_tutor_class_draft_'.$teacher->teacher_id, false);

        $draft = [
            'version' => 1,
            'step' => 3,
            'savedAt' => now()->getTimestamp() * 1000,
            'values' => [
                'package_type' => 'instant',
                'grade_level' => 'SMP',
                'subject_name' => 'Matematika',
                'package_name' => 'Draft Aljabar Tutor',
                'target_learning' => 'Memahami persamaan linear.',
                'learning_method' => 'online_zoom',
                'target_capacity' => '12',
                'zoom_meeting_url' => 'https://zoom.us/j/123456789',
                'availability_slot_ids' => [$slot->slot_id],
            ],
        ];

        $this->actingAs($user)->postJson(route('tutor.classes.draft.store'), ['draft' => $draft])
            ->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('tutor_class_drafts', ['teacher_id' => $teacher->teacher_id]);
        $this->actingAs($user)->get(route('tutor.classes.create', ['step' => 3]))
            ->assertOk()
            ->assertSee('Draft Aljabar Tutor')
            ->assertSee('initialStep: 3', false)
            ->assertSee($slot->slot_id);
    }

    public function test_master_availability_returns_to_create_class_step_three_and_renders_new_slots(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $stepThreeUrl = route('tutor.classes.create', ['step' => 3]);

        $this->actingAs($user)->get(route('tutor.availability', ['return_to' => 'create_class', 'step' => 3]))
            ->assertOk()
            ->assertSee($stepThreeUrl)
            ->assertSee('Kembali ke Langkah 3 Buat Kelas');

        $response = $this->actingAs($user)->post(route('tutor.availability.store'), [
            'day_of_week' => 'Thursday',
            'start_time' => '20:00',
            'end_time' => '21:00',
            'return_to' => 'create_class',
            'step' => 3,
        ]);

        $response->assertRedirect(route('tutor.availability', [
            'day' => 'Thursday', 'return_to' => 'create_class', 'step' => 3,
        ]));

        $slot = $teacher->availabilitySlots()->where('day_of_week', 'Thursday')->firstOrFail();
        $this->actingAs($user)->get(route('tutor.classes.create', ['step' => 3]))
            ->assertOk()
            ->assertSee($slot->slot_id)
            ->assertSee('data-step-panel="3"', false);
    }

    public function test_master_availability_defaults_back_to_tutor_dashboard(): void
    {
        [$user] = $this->makeTutor();

        $this->actingAs($user)->get(route('tutor.availability', ['return_to' => 'dashboard']))
            ->assertOk()
            ->assertSee(route('tutor.dashboard'))
            ->assertSee('Kembali ke Beranda Tutor');
    }

    public function test_published_online_and_home_visit_classes_appear_in_booking_subject_choices_case_insensitively(): void
    {
        [$user, $teacher] = $this->makeTutor([], ['approved_levels' => ['smp'], 'verified_levels' => ['SMP']]);
        Subject::create([
            'name' => 'Matematika',
            'grade_level' => 'sMp',
            'category' => 'Akademik',
            'is_active' => true,
        ]);

        foreach ([
            ['method' => 'online', 'storedMethod' => 'online_zoom', 'day' => 'Friday', 'start' => '18:00', 'end' => '19:00', 'capacity' => 20,
                'zoom_meeting_url' => 'https://zoom.us/j/123456789'],
            ['method' => 'home_visit', 'storedMethod' => 'home_visit', 'day' => 'Saturday', 'start' => '10:00', 'end' => '11:00', 'capacity' => 5,
                'default_location' => 'Area Makassar, hubungi sebelum kunjungan.'],
        ] as $class) {
            $this->seedTariff($teacher, $class['storedMethod']);
            $slot = $this->makeTutorSlot($teacher, [
                'day_of_week' => $class['day'],
                'start_time' => $class['start'].':00',
                'end_time' => $class['end'].':00',
            ]);

            $payload = [
                'package_type' => 'instant',
                'grade_level' => 'SMP',
                'package_name' => 'Program '.$class['storedMethod'],
                'subject_name' => 'Matematika',
                'target_learning' => 'Menguasai konsep dasar matematika.',
                'learning_method' => $class['method'],
                'target_capacity' => $class['capacity'],
                'availability_slot_ids' => [$slot->slot_id],
            ] + array_intersect_key($class, array_flip(['zoom_meeting_url', 'default_location']));

            $this->actingAs($user)->post(route('tutor.classes.store'), $payload)->assertRedirect();
            $package = ModulePackage::query()->where('teacher_id', $teacher->teacher_id)
                ->where('package_name', $payload['package_name'])->firstOrFail();

            $this->assertSame($class['storedMethod'], $package->learning_method);
            $this->assertSame($class['zoom_meeting_url'] ?? null, $package->zoom_meeting_url);
            $this->assertSame($class['default_location'] ?? null, $package->home_visit_area);

            $this->makePackageReadyToPublish($teacher, $package);
            $this->actingAs($user)->post(route('tutor.classes.materials.publish', $package->package_id))->assertRedirect();
            $this->assertTrue($package->fresh()->is_open);

            $methodFilter = $class['storedMethod'] === 'online_zoom' ? 'online' : 'HOME_VISIT';
            $this->getJson('/api/student/catalog/subjects?education_level=sMp&learning_method='.$methodFilter)
                ->assertOk()
                ->assertJsonPath('data.0', 'Matematika');
        }
    }

    private function seedTariff(Teacher $teacher, string $method): void
    {
        $ratePackage = ModulePackage::create([
            'package_name' => 'Tarif '.$method,
            'grade_level' => 'SMP',
            'learning_method' => $method,
            'base_price' => 60000,
            'is_active' => true,
        ]);
        MasterTariff::create([
            'rate_id' => (string) Str::uuid(),
            'package_id' => $ratePackage->package_id,
            'teacher_level' => $teacher->teacher_level,
            'learning_method' => $method,
            'category_rate' => 60000,
        ]);
    }

    private function makePackageReadyToPublish(Teacher $teacher, ModulePackage $package): void
    {
        TutorClassSessionContent::create([
            'package_id' => $package->package_id,
            'session_number' => 1,
            'topic' => 'Dasar Matematika',
            'learning_target' => 'Mampu menerapkan konsep dasar.',
        ]);
        LearningMaterial::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'session_number' => 1,
            'title' => 'Modul Matematika',
            'material_type' => 'pdf',
            'file_or_url' => 'learning-modules/matematika.pdf',
            'created_at' => now(),
        ]);
        foreach (range(1, 3) as $number) {
            QuestionBank::create([
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'session_number' => 1,
                'exam_type' => 'pre_test',
                'category' => 'Matematika',
                'question_text' => 'Pertanyaan '.$number,
                'option_a' => 'A', 'option_b' => 'B', 'option_c' => 'C', 'option_d' => 'D',
                'answer_key' => 'a',
            ]);
        }
    }
}
