<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAvailabilitySlot;
use App\Models\QuestionBank;
use App\Models\TutorClassSessionContent;
use App\Models\TutorPackageRate;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\Support\CreatesBookablePackages;
use Tests\TestCase;

class StudentBookingTest extends TestCase
{
    use RefreshDatabase;
    use CreatesBookablePackages;
    use TutorTestFixtures;

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

    public function test_newly_published_tutor_class_appears_in_catalog_and_step_one_and_can_be_booked(): void
    {
        Storage::fake('public');
        [$tutorUser, $teacher] = $this->makeTutor([], ['approved_levels' => ['SMP']]);
        $slot = $this->makeTutorSlot($teacher, ['learning_method' => 'online_zoom']);
        $this->seedTariffForNewClass($teacher, 'online_zoom', 60000);
        Subject::create(['name' => 'Fisika', 'grade_level' => 'SMP', 'category' => 'Akademik', 'is_active' => true]);

        $this->actingAs($tutorUser)->post(route('tutor.classes.store'), [
            'package_type' => 'instant',
            'grade_level' => 'SMP',
            'package_name' => 'Fisika Kelas UAT',
            'subject_name' => 'Fisika',
            'target_learning' => 'Memahami hukum gerak.',
            'learning_method' => 'online_zoom',
            'target_capacity' => 10,
            'availability_slot_ids' => [$slot->slot_id],
        ])->assertRedirect();

        $package = ModulePackage::query()->where('teacher_id', $teacher->teacher_id)
            ->where('package_name', 'Fisika Kelas UAT')->firstOrFail();
        TutorClassSessionContent::create([
            'package_id' => $package->package_id, 'session_number' => 1,
            'topic' => 'Hukum Gerak', 'learning_target' => 'Memahami hukum gerak Newton.',
        ]);
        LearningMaterial::create([
            'teacher_id' => $teacher->teacher_id, 'package_id' => $package->package_id,
            'session_number' => 1, 'title' => 'Modul Hukum Gerak', 'material_type' => 'pdf',
            'file_or_url' => 'materials/hukum-gerak.pdf', 'created_at' => now(),
        ]);
        foreach (range(1, 3) as $number) {
            QuestionBank::create([
                'teacher_id' => $teacher->teacher_id, 'package_id' => $package->package_id,
                'session_number' => 1, 'exam_type' => 'pre_test', 'category' => 'Fisika',
                'question_text' => "Soal fisika {$number}", 'option_a' => 'A', 'option_b' => 'B',
                'option_c' => 'C', 'option_d' => 'D', 'answer_key' => 'A',
            ]);
        }
        $this->actingAs($tutorUser)->post(route('tutor.classes.materials.publish', $package->package_id))
            ->assertRedirect(route('tutor.classes'));

        // Simulasikan data legacy dengan variasi kapitalisasi; pencocokan di katalog harus tetap berhasil.
        $teacher->update(['approved_levels' => ['sMp']]);
        $package->update(['grade_level' => 'sMp']);
        $slot->update(['is_active' => false]);

        $subjectsResponse = $this->getJson('/api/student/catalog/subjects?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonFragment(['Fisika']);
        $this->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom&subject=Fisika')
            ->assertOk()->assertJsonFragment(['package_name' => 'Fisika Kelas UAT'])
            ->assertJsonPath('data.0.packages.0.availability_slots', []);

        [$studentUser] = $this->makeStudent([], ['education_level' => 'SMP']);
        $this->actingAs($studentUser)->get(route('student.booking'))
            ->assertOk()->assertSee(route('api.student.catalog.subjects'), false);
        // Endpoint ini adalah sumber opsi yang dimuat oleh Alpine Step 1.
        $this->actingAs($studentUser)->getJson('/api/student/catalog/subjects?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonFragment(['Fisika']);
        $this->getJson('/api/student/booking/tutor-slots/'.$teacher->teacher_id.'?learning_method=online_zoom&package_id='.$package->package_id)
            ->assertOk()->assertExactJson(['success' => true, 'data' => []]);

        // Setelah tutor membuka kembali slotnya, pembelajar dapat meneruskan pilihan hingga checkout.
        $slot->update(['is_active' => true]);
        config(['services.xendit.secret_key' => 'test-secret']);
        Http::fake(['https://api.xendit.co/v2/invoices' => Http::response([
            'id' => 'invoice-tutor-class-uat', 'invoice_url' => 'https://pay.example.test/tutor-class-uat',
        ], 200)]);
        $checkout = $this->postJson('/api/student/booking/checkout', [
            'teacher_id' => $teacher->teacher_id,
            'package_id' => $package->package_id,
            'learning_method' => 'online_zoom',
            'session_package' => 1,
            'format' => 'solo',
            'selected_slot_id' => $slot->slot_id,
            'payment_method' => 'qris',
        ])->assertCreated()->assertJsonPath('data.payment_status', 'pending');

        $this->assertDatabaseHas('orders', ['order_id' => $checkout->json('data.order_id'), 'package_id' => $package->package_id]);
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
            'teacher_id' => null,
            'package_name' => 'Matematika SMP',
            'grade_level' => 'SMP',
            'base_price' => 50000,
            'supported_session_packages' => '1,4',
            'is_published' => true,
            'is_open' => true,
            'is_active' => true,
        ]);
        $package->update(['teacher_id' => $teacher->teacher_id]);
        foreach (range(1, 4) as $sessionNumber) {
            TutorClassSessionContent::create([
                'package_id' => $package->package_id,
                'session_number' => $sessionNumber,
                'topic' => "Topik {$sessionNumber}",
                'learning_target' => "Target {$sessionNumber}",
            ]);
            LearningMaterial::create([
                'teacher_id' => $teacher->teacher_id,
                'package_id' => $package->package_id,
                'session_number' => $sessionNumber,
                'title' => "Aljabar {$sessionNumber}",
                'material_type' => 'pdf',
                'file_or_url' => "materials/aljabar-{$sessionNumber}.pdf",
            ]);
            foreach (range(1, 3) as $questionNumber) {
                QuestionBank::create([
                    'teacher_id' => $teacher->teacher_id,
                    'package_id' => $package->package_id,
                    'session_number' => $sessionNumber,
                    'exam_type' => 'pre_test',
                    'category' => 'Matematika',
                    'question_text' => "Pertanyaan {$sessionNumber}-{$questionNumber}",
                    'answer_key' => 'A',
                ]);
            }
        }
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

        $this->makePackageReadyForEnrollment($teacher, $package);

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

    private function seedTariffForNewClass(Teacher $teacher, string $method, int $amount): void
    {
        $tariffPackage = ModulePackage::create([
            'package_name' => 'Tarif Master '.$method, 'grade_level' => 'SMP',
            'learning_method' => $method, 'base_price' => $amount, 'is_active' => true,
        ]);
        MasterTariff::create([
            'rate_id' => (string) Str::uuid(), 'package_id' => $tariffPackage->package_id,
            'teacher_level' => $teacher->teacher_level, 'learning_method' => $method,
            'category_rate' => $amount,
        ]);
    }
}
