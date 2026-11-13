<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TutorAuthAndProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_reg_001_registers_profile_and_pdf_documents_pending_review(): void
    {
        Storage::fake('public');

        $response = $this->postJson('/api/tutors/register', $this->validRegistrationPayload());

        $response->assertCreated()->assertJsonPath('data.status_cv', 'pending');
        $user = User::where('email', 'calon.tutor@example.test')->firstOrFail();
        $teacher = Teacher::where('user_id', $user->user_id)->firstOrFail();

        $this->assertFalse($user->is_active);
        $this->assertSame('pending', $teacher->cv_status);
        $this->assertDatabaseHas('teacher_level_verifications', [
            'teacher_id' => $teacher->teacher_id,
            'jenjang' => 'SMP',
            'status_verifikasi' => 'menunggu_review',
        ]);
        Storage::disk('public')->assertExists($teacher->cv_url);
        Storage::disk('public')->assertExists($teacher->identity_document_url);
        Storage::disk('public')->assertExists($teacher->transcript_url);
    }

    public function test_tutor_reg_002_rejects_non_pdf_cv(): void
    {
        Storage::fake('public');
        $payload = $this->validRegistrationPayload();
        $payload['cv_file'] = UploadedFile::fake()->image('cv.jpg');

        $this->postJson('/api/tutors/register', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cv_file');
    }

    public function test_tutor_reg_003_rejects_duplicate_email(): void
    {
        Storage::fake('public');
        User::factory()->create(['email' => 'calon.tutor@example.test']);

        $this->postJson('/api/tutors/register', $this->validRegistrationPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_susp_002_inactive_tutor_is_denied_login_with_account_notice(): void
    {
        User::factory()->create([
            'email' => 'tutor.nonaktif@example.test',
            'password_hash' => Hash::make('password123'),
            'role' => 'teacher',
            'is_active' => false,
            'suspension_reason' => 'Pelanggaran SOP',
        ]);

        $this->post('/login', [
            'email' => 'tutor.nonaktif@example.test',
            'password' => 'password123',
            'user_role' => 'tutor',
        ])->assertRedirect()
            ->assertSessionHasErrors(['email' => 'Akun Anda telah dibekukan sementara. Silakan hubungi Admin untuk proses klarifikasi.']);
    }

    public function test_active_tutor_login_redirects_to_tutor_dashboard(): void
    {
        User::factory()->create([
            'email' => 'tutor.aktif@example.test',
            'password_hash' => Hash::make('password123'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'tutor.aktif@example.test',
            'password' => 'password123',
            'user_role' => 'tutor',
        ])->assertRedirect(route('tutor.dashboard'));
    }

    public function test_upgrade_001_exposes_tutor_level_request_submission_route(): void
    {
        $this->assertTrue(
            Route::has('tutor.level-requests.store'),
            'Belum ada route untuk mengajukan kenaikan jenjang tutor.'
        );
    }

    private function validRegistrationPayload(): array
    {
        return [
            'full_name' => 'Calon Tutor Kawal',
            'email' => 'calon.tutor@example.test',
            'phone_number' => '081234567890',
            'domicile_address' => 'Banjarmasin',
            'university' => 'Universitas Uji',
            'study_program' => 'Pendidikan Matematika',
            'semester' => 'Semester 6',
            'selected_levels' => ['SMP'],
            'focus_subject' => 'Matematika',
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'ktp_ktm_file' => UploadedFile::fake()->create('identitas.pdf', 100, 'application/pdf'),
            'transcript_file' => UploadedFile::fake()->create('transkrip.pdf', 100, 'application/pdf'),
        ];
    }
}
