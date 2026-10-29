<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\TutorPackageRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_and_catalog_filter_by_level_subject_and_learning_method(): void
    {
        [$studentUser, $teacher, $package] = $this->marketplace();

        $response = $this->actingAs($studentUser)->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom&search=Matematika');

        $response->assertOk()
            ->assertJsonPath('filters.education_level', 'SMP')
            ->assertJsonPath('filters.learning_method', 'online_zoom')
            ->assertJsonPath('data.0.teacher_id', $teacher->teacher_id)
            ->assertJsonPath('data.0.packages.0.package_id', $package->package_id);
    }

    public function test_catalog_excludes_inactive_package_and_unsupported_session_package_is_rejected(): void
    {
        [$studentUser] = $this->marketplace();
        $inactivePackage = ModulePackage::create([
            'package_name' => 'Paket Nonaktif', 'grade_level' => 'SMP', 'is_active' => false,
        ]);

        $this->actingAs($studentUser)
            ->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()
            ->assertJsonMissing(['package_id' => $inactivePackage->package_id]);

        $payload = $this->bookingPayload(['session_package' => 2]);
        $this->postJson('/api/student/booking/calculate', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('session_package');
    }

    public function test_home_visit_requires_address_and_online_checkout_does_not_expose_physical_location(): void
    {
        [$studentUser] = $this->marketplace(['saved_address' => null]);
        config(['services.xendit.secret_key' => 'test-secret']);
        Http::fake(['https://api.xendit.co/v2/invoices' => Http::response(['id' => 'invoice-online', 'invoice_url' => 'https://pay.example.test/online'], 200)]);

        $this->actingAs($studentUser)
            ->postJson('/api/student/booking/checkout', $this->bookingPayload(['learning_method' => 'home_visit']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('learning_method');

        $response = $this->actingAs($studentUser)
            ->postJson('/api/student/booking/checkout', $this->bookingPayload(['learning_method' => 'online_zoom']))
            ->assertCreated()
            ->assertJsonPath('data.payment_status', 'pending');

        $this->assertDatabaseHas('orders', [
            'order_id' => $response->json('data.order_id'),
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $response->json('data.order_id'),
            'payment_status' => 'pending',
        ]);
    }

    public function test_squad_capacity_limits_and_checkout_generates_invite_code(): void
    {
        [$studentUser] = $this->marketplace();
        config(['services.xendit.secret_key' => 'test-secret']);
        $this->actingAs($studentUser);

        $this->postJson('/api/student/booking/calculate', $this->bookingPayload([
            'learning_method' => 'online_zoom', 'format' => 'host', 'target_capacity' => 20,
        ]))->assertOk()->assertJsonPath('data.discount_percentage', 35);

        $this->postJson('/api/student/booking/calculate', $this->bookingPayload([
            'learning_method' => 'home_visit', 'format' => 'host', 'target_capacity' => 6,
        ]))->assertUnprocessable()->assertJsonValidationErrors('target_capacity');

        Http::fake(['https://api.xendit.co/v2/invoices' => Http::response(['id' => 'invoice-squad', 'invoice_url' => 'https://pay.example.test/squad'], 200)]);
        $response = $this->postJson('/api/student/booking/checkout', $this->bookingPayload([
            'learning_method' => 'online_zoom', 'format' => 'host', 'target_capacity' => 2,
        ]))->assertCreated();

        $this->assertNotEmpty($response->json('data.squad_code'));
        $this->assertDatabaseHas('squads', ['squad_code' => $response->json('data.squad_code')]);
    }

    public function test_booked_tutor_slot_is_reported_unavailable(): void
    {
        [$studentUser, $teacher, $package, $slot] = $this->marketplace();
        $otherUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::create(['user_id' => $otherUser->user_id, 'education_level' => 'SMP']);
        Order::create([
            'student_id' => $otherStudent->student_id,
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'selected_slot_id' => $slot->slot_id,
            'total_amount' => 50000,
            'payment_status' => 'settled',
            'order_status' => 'confirmed',
        ]);

        $this->actingAs($studentUser)
            ->getJson("/api/student/booking/tutor-slots/{$teacher->teacher_id}?learning_method=online_zoom")
            ->assertOk()
            ->assertJsonPath('data.0.is_available', false);
    }

    private function marketplace(array $studentAttributes = []): array
    {
        $studentUser = User::factory()->create(['role' => 'student']);
        Student::create(array_merge([
            'user_id' => $studentUser->user_id,
            'education_level' => 'SMP',
            'saved_address' => 'Jl. Contoh 10',
            'landmark' => 'Dekat sekolah',
            'latitude' => -3.31,
            'longitude' => 114.59,
        ], $studentAttributes));

        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->user_id,
            'cv_status' => 'approved',
            'teacher_level' => 'junior',
            'approved_levels' => ['SMP'],
            'focus_subject' => 'Matematika',
            'is_active' => true,
        ]);
        $package = ModulePackage::create([
            'package_name' => 'Matematika SMP',
            'grade_level' => 'SMP',
            'base_price' => 50000,
            'is_active' => true,
        ]);
        LearningMaterial::create([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'title' => 'Aljabar',
            'material_type' => 'pdf',
            'file_or_url' => 'materials/aljabar.pdf',
        ]);
        TutorPackageRate::create([
            'package_id' => $package->package_id,
            'teacher_level' => 'junior',
            'learning_method' => 'online_zoom',
            'category_rate' => 50000,
        ]);
        TutorPackageRate::create([
            'package_id' => $package->package_id,
            'teacher_level' => 'junior',
            'learning_method' => 'home_visit',
            'category_rate' => 60000,
        ]);
        $slot = TeacherAvailabilitySlot::create([
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => 'Monday',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'learning_method' => 'online_zoom',
            'is_active' => true,
        ]);
        TeacherAvailabilitySlot::create([
            'teacher_id' => $teacher->teacher_id,
            'day_of_week' => 'Tuesday',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'learning_method' => 'home_visit',
            'is_active' => true,
        ]);

        return [$studentUser, $teacher, $package, $slot];
    }

    private function bookingPayload(array $overrides = []): array
    {
        $package = ModulePackage::firstOrFail();
        $teacher = Teacher::firstOrFail();
        $slot = TeacherAvailabilitySlot::firstOrFail();

        return array_merge([
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'format' => 'solo',
            'selected_slot_id' => $slot->slot_id,
        ], $overrides);
    }
}
