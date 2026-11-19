<?php

namespace Tests\Feature;

use App\Models\MasterTariff;
use App\Models\ModulePackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class TutorClassWizardTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    public function test_tarif_001_master_tariffs_are_read_only_and_displayed_for_tutor_level(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $this->seedTariff($teacher, 'online_zoom', 60000);
        $this->seedTariff($teacher, 'home_visit', 75000);

        $response = $this->actingAs($user)->get(route('tutor.classes.create'));

        $response->assertOk()
            ->assertSee('tariffs =', false)
            ->assertSee('SMP|online_zoom', false)
            ->assertSee('SMP|home_visit', false)
            ->assertSee('60000', false)
            ->assertSee('75000', false);
        $this->assertDatabaseCount('master_tariffs', 2);
    }

    public function test_tariff_defaults_cover_both_modes_and_home_visit_quota_is_capped_in_step_two(): void
    {
        [$user] = $this->makeTutor();

        $this->actingAs($user)->get(route('tutor.classes.create'))
            ->assertOk()
            ->assertSee('SMP|home_visit', false)
            ->assertSee('data-capacity-option="5"', false)
            ->assertSee('data-capacity-option="20"', false)
            ->assertSee('const maximum = normalizeMethod(method.value) === \'home_visit\' ? 5 : 20', false)
            ->assertSee('Estimasi nilai paket per siswa', false);
    }

    public function test_slot_001_tutor_can_add_one_hour_master_availability(): void
    {
        [$user, $teacher] = $this->makeTutor();

        $this->actingAs($user)->post(route('tutor.availability.store'), [
            'day_of_week' => 'Friday',
            'start_time' => '18:00',
            'end_time' => '19:00',
        ])->assertRedirect(route('tutor.availability', ['day' => 'Friday']));

        $this->assertDatabaseHas('teacher_availability_slots', [
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => 'Friday',
            'start_time' => '18:00:00',
            'end_time' => '19:00:00',
            'is_active' => true,
        ]);
    }

    public function test_wiz_001_step_one_exposes_only_verified_grade_choices_and_subject_catalog(): void
    {
        [$user] = $this->makeTutor([], ['approved_levels' => ['SD', 'SMP']]);

        $this->actingAs($user)->get(route('tutor.classes.create'))
            ->assertOk()
            ->assertSee('data-grade-choice="SD"', false)
            ->assertSee('data-grade-choice="SMP"', false)
            ->assertSee('data-subject-choice=', false);
    }

    public function test_wiz_002_rejects_capacity_above_each_learning_method_limit(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $slot = $this->makeTutorSlot($teacher);
        $this->seedTariff($teacher, 'online_zoom', 60000);
        $this->seedTariff($teacher, 'home_visit', 75000);

        foreach ([['online_zoom', 21], ['home_visit', 6]] as [$method, $capacity]) {
            $this->actingAs($user)->from(route('tutor.classes.create'))
                ->post(route('tutor.classes.store'), $this->classPayload($slot->slot_id, $method, $capacity))
                ->assertSessionHasErrors('target_capacity');
        }
    }

    public function test_wizard_can_create_home_visit_class_using_grade_tariff_not_tutor_rank(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $slot = $this->makeTutorSlot($teacher, ['learning_method' => 'home_visit']);
        $this->seedTariff($teacher, 'home_visit', 75000);

        $response = $this->actingAs($user)->post(route('tutor.classes.store'), $this->classPayload($slot->slot_id, 'home_visit', 5, 'Kelas Home Visit'));

        $package = ModulePackage::query()->where('teacher_id', $teacher->teacher_id)->where('package_name', 'Kelas Home Visit')->firstOrFail();
        $response->assertRedirect(route('tutor.classes.materials', ['packageId' => $package->package_id, 'session' => 1]));
        $this->assertDatabaseHas('master_tariffs', [
            'package_id' => $package->package_id,
            'teacher_level' => $teacher->teacher_level,
            'learning_method' => 'home_visit',
            'category_rate' => 75000,
        ]);
    }

    public function test_wiz_003_master_slot_cannot_be_attached_to_two_class_programs(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $slot = $this->makeTutorSlot($teacher);
        $this->seedTariff($teacher, 'online_zoom', 60000);

        $this->actingAs($user)->post(route('tutor.classes.store'), $this->classPayload($slot->slot_id))
            ->assertRedirect();
        $second = $this->actingAs($user)->from(route('tutor.classes.create'))
            ->post(route('tutor.classes.store'), $this->classPayload($slot->slot_id, 'online_zoom', 20, 'Program Kedua'));

        $second->assertSessionHasErrors('availability_slot_ids');
        $this->assertSame(1, DB::table('module_package_availability_slots')->where('slot_id', $slot->slot_id)->count());
    }

    public function test_wiz_004_create_class_page_shows_empty_state_without_master_slots(): void
    {
        [$user] = $this->makeTutor();

        $this->actingAs($user)->get(route('tutor.classes.create'))
            ->assertOk()
            ->assertSee('Belum ada slot aktif di Master Availability.');
    }

    public function test_wiz_005_new_class_is_saved_as_draft_and_opens_material_studio(): void
    {
        [$user, $teacher] = $this->makeTutor();
        $slot = $this->makeTutorSlot($teacher);
        $this->seedTariff($teacher, 'online_zoom', 60000);

        $response = $this->actingAs($user)->post(route('tutor.classes.store'), $this->classPayload($slot->slot_id));

        $package = ModulePackage::query()->where('teacher_id', $teacher->teacher_id)->where('package_name', 'Program Uji Tutor')->firstOrFail();
        $response->assertRedirect(route('tutor.classes.materials', ['packageId' => $package->package_id, 'session' => 1]));
        $this->assertFalse($package->is_published);
        $this->assertFalse($package->is_open);
        $this->assertDatabaseHas('module_package_availability_slots', [
            'package_id' => $package->package_id,
            'slot_id' => $slot->slot_id,
        ]);
    }

    private function seedTariff($teacher, string $method, int $amount): void
    {
        $package = ModulePackage::create([
            'package_name' => 'Tarif '.$method,
            'grade_level' => 'SMP',
            'learning_method' => $method,
            'base_price' => $amount,
            'is_active' => true,
        ]);

        MasterTariff::create([
            'rate_id' => (string) Str::uuid(),
            'package_id' => $package->package_id,
            'teacher_level' => $teacher->teacher_level,
            'learning_method' => $method,
            'category_rate' => $amount,
        ]);
    }

    private function classPayload(string $slotId, string $method = 'online_zoom', int $capacity = 20, string $name = 'Program Uji Tutor'): array
    {
        return [
            'package_type' => 'instant',
            'grade_level' => 'SMP',
            'package_name' => $name,
            'subject_name' => 'Matematika',
            'target_learning' => 'Mencapai pemahaman materi.',
            'learning_method' => $method,
            'target_capacity' => $capacity,
            'default_location' => $method === 'home_visit' ? 'Alamat Tutor, Banjarmasin' : null,
            'availability_slot_ids' => [$slotId],
        ];
    }
}
