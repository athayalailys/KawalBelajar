<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherLevelRequest;
use App\Models\TeacherWorkLog;
use App\Models\TutorPackageRate;
use App\Models\User;
use App\Mail\TutorApprovedCredentialsMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\Support\CreatesBookablePackages;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;
    use CreatesBookablePackages;

    private function makeAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Admin::create(['user_id' => $user->user_id, 'position' => 'Administrator']);

        return $user;
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->makeAdmin();
        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'user_role' => 'siswa',
        ])->assertRedirect(route('admin.dashboard'));

        foreach (['admin.dashboard', 'admin.tutors', 'admin.tariffs', 'admin.subjects', 'admin.finance'] as $page) {
            $this->get(route($page))->assertOk();
        }
    }

    public function test_non_admin_cannot_access_admin_portal_routes(): void
    {
        [$student] = $this->makeStudent();
        [$tutor] = $this->makeTutor();
        $routes = ['admin.dashboard', 'admin.tutors', 'admin.tariffs', 'admin.subjects', 'admin.finance'];

        foreach ([$student, $tutor] as $user) {
            foreach ($routes as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }

        auth()->logout();
        foreach ($routes as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_ver_001_admin_approves_tutor_documents_and_assigns_verified_levels(): void
    {
        Mail::fake();
        [$user, $teacher] = $this->makeTutor(['is_active' => false], [
            'cv_status' => 'pending', 'approved_levels' => [], 'verified_levels' => [], 'requested_levels' => ['SD', 'SMP'],
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.approve', $teacher->teacher_id), ['approved_levels' => ['SD', 'SMP']])
            ->assertOk()
            ->assertJsonPath('message', 'Tutor disetujui dan email kredensial diteruskan ke server email.')
            ->assertJsonPath('mail_sent', true);

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'approved']);
        $this->assertSame(['SD', 'SMP'], $teacher->fresh()->verified_levels);
        $this->assertTrue($user->fresh()->is_active);
        $approvedMail = null;
        Mail::assertSent(TutorApprovedCredentialsMail::class, function (TutorApprovedCredentialsMail $mail) use (&$approvedMail, $user) {
            $approvedMail = $mail;
            return $mail->hasTo($user->email)
                && $mail->email === $user->email
                && $mail->verifiedLevels === ['SD', 'SMP'];
        });

        $this->assertSame($user->email, session('approved_credentials.email'));
        $this->assertSame(['SD', 'SMP'], session('approved_credentials.verified_levels'));
        $this->assertTrue(session('approved_credentials.mail_sent'));
        $this->get(route('admin.tutors'))
            ->assertOk()
            ->assertSee('data-approved-credentials', false)
            ->assertSee($approvedMail->temporaryPassword);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => $approvedMail->temporaryPassword,
            'user_role' => 'tutor',
        ])->assertRedirect(route('tutor.dashboard'));
    }

    public function test_approval_does_not_claim_delivery_when_log_mailer_is_configured(): void
    {
        Mail::fake();
        config([
            'mail.default' => 'log',
            'mail.mailers.log.transport' => 'log',
        ]);
        [, $teacher] = $this->makeTutor(['is_active' => false], [
            'cv_status' => 'pending',
            'approved_levels' => [],
            'verified_levels' => [],
            'requested_levels' => ['SMP'],
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.approve', $teacher->teacher_id), ['approved_levels' => ['SMP']])
            ->assertOk()
            ->assertJsonPath('mail_sent', false)
            ->assertJsonPath('message', "Email belum dikirim: transport mail 'log' tidak mengirim ke inbox. Konfigurasikan SMTP di .env, lalu jalankan php artisan config:clear.");

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'approved']);
        Mail::assertNothingOutgoing();
    }

    public function test_ver_002_admin_rejects_tutor_registration_with_specific_reason(): void
    {
        Mail::fake();
        [, $teacher] = $this->makeTutor([], ['cv_status' => 'pending', 'requested_levels' => ['SMP']]);
        $admin = $this->makeAdmin();

        $reason = 'Foto KHS buram dan kualifikasi tidak memenuhi standar';
        $this->actingAs($admin)->postJson(route('admin.tutors.reject', $teacher->teacher_id), ['reason' => $reason])
            ->assertOk();

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'rejected', 'review_note' => $reason]);
        Mail::assertSent(\App\Mail\TutorRejectedMail::class);
    }

    public function test_ver_003_admin_cannot_reject_tutor_without_rejection_reason(): void
    {
        [, $teacher] = $this->makeTutor([], ['cv_status' => 'pending', 'requested_levels' => ['SMP']]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.reject', $teacher->teacher_id), ['reason' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'pending']);
    }

    public function test_susp_001_admin_suspends_tutor_revokes_access_and_unpublishes_classes(): void
    {
        [$tutorUser, $teacher] = $this->makeTutor();
        [$studentUser] = $this->makeStudent();
        $package = $this->makeTutorPackage($teacher, ['is_published' => true, 'is_open' => true]);
        $this->makePackageReadyForEnrollment($teacher, $package);
        $slot = $this->makeTutorSlot($teacher, ['learning_method' => 'online_zoom']);
        TutorPackageRate::create([
            'package_id' => $package->package_id,
            'teacher_level' => $teacher->teacher_level,
            'learning_method' => 'online_zoom',
            'category_rate' => 60000,
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($studentUser)->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonFragment(['package_id' => $package->package_id]);

        $this->actingAs($admin)->postJson(route('admin.tutors.suspend', $teacher->teacher_id), ['reason' => 'pelanggaran SOP'])->assertOk();
        $this->assertFalse($tutorUser->fresh()->is_active);
        $this->assertSame('pelanggaran SOP', $tutorUser->fresh()->suspension_reason);
        $this->assertDatabaseHas('module_packages', ['package_id' => $package->package_id, 'is_published' => false, 'is_open' => false]);

        $this->actingAs($tutorUser->fresh())->get(route('tutor.dashboard'))->assertStatus(423);

        $this->actingAs($studentUser)->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonMissing(['package_id' => $package->package_id]);
    }

    public function test_upgrade_002_admin_approves_tutor_level_upgrade_and_unlocks_class_creation(): void
    {
        Mail::fake();
        [$tutorUser, $teacher] = $this->makeTutor([], ['approved_levels' => ['SD', 'SMP'], 'verified_levels' => ['SD', 'SMP']]);
        $request = TeacherLevelRequest::create([
            'teacher_id' => $teacher->teacher_id,
            'requested_level' => 'UMUM',
            'request_status' => 'pending_upgrade',
            'created_at' => now(),
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.level-requests.approve', $request->request_id))
            ->assertOk()->assertJsonPath('data.verified_levels.2', 'UMUM');

        $this->assertSame(['SD', 'SMP', 'UMUM'], $teacher->fresh()->verified_levels);
        $this->assertDatabaseHas('teacher_level_requests', ['request_id' => $request->request_id, 'request_status' => 'approved']);
        $this->actingAs($tutorUser)->get(route('tutor.classes.create'))->assertOk()->assertSee('data-grade-choice="UMUM"', false);
    }

    public function test_tarif_002_admin_updates_master_tariff_records_audit_and_new_class_uses_rate(): void
    {
        [$tutor, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher, ['grade_level' => 'SMP', 'learning_method' => 'online_zoom']);
        $tariff = MasterTariff::create([
            'rate_id' => (string) \Illuminate\Support\Str::uuid(),
            'package_id' => $package->package_id, 'teacher_level' => $teacher->teacher_level,
            'grade_level' => 'SMP', 'method' => 'online_zoom', 'learning_method' => 'online_zoom',
            'category_rate' => 50000, 'base_rate' => 50000,
        ]);
        $admin = $this->makeAdmin();

        foreach ([0, -1000] as $invalidAmount) {
            $this->actingAs($admin)->putJson(route('admin.tariffs.update'), [
                'grade_level' => 'SMP', 'learning_method' => 'online_zoom', 'amount' => $invalidAmount, 'reason' => 'Koreksi tarif',
            ])->assertUnprocessable()->assertJsonValidationErrors('amount');
        }
        $this->putJson(route('admin.tariffs.update'), [
            'grade_level' => 'SMP', 'learning_method' => 'online_zoom', 'amount' => 80000, 'reason' => 'Penyesuaian tarif tahunan',
        ])->assertOk();

        $this->assertDatabaseHas('master_tariffs', ['rate_id' => $tariff->rate_id, 'category_rate' => 80000]);
        $this->assertDatabaseHas('tariff_audit_logs', [
            'admin_id' => $admin->admin->admin_id,
            'old_price' => 50000,
            'new_price' => 80000,
            'justification_notes' => 'Penyesuaian tarif tahunan',
        ]);
        $this->assertNotNull(\App\Models\TariffAuditLog::query()->where('admin_id', $admin->admin->admin_id)->first()?->changed_at);

        $slot = $this->makeTutorSlot($teacher, ['learning_method' => 'online_zoom']);
        $this->actingAs($tutor)->post(route('tutor.classes.store'), [
            'package_type' => 'instant', 'grade_level' => 'SMP', 'package_name' => 'Kelas Tarif Terbaru',
            'subject_name' => 'Matematika', 'target_learning' => 'Memahami konsep matematika.',
            'learning_method' => 'online_zoom', 'target_capacity' => 20,
            'availability_slot_ids' => [$slot->slot_id],
        ])->assertRedirect();
        $this->assertDatabaseHas('module_packages', ['teacher_id' => $teacher->teacher_id, 'package_name' => 'Kelas Tarif Terbaru', 'base_price' => 80000]);
    }

    public function test_tarif_003_admin_rejects_zero_or_negative_tariff_values_without_audit(): void
    {
        [, $teacher] = $this->makeTutor();
        $package = $this->makeTutorPackage($teacher, ['grade_level' => 'SMP', 'learning_method' => 'online_zoom']);
        MasterTariff::create([
            'rate_id' => (string) \Illuminate\Support\Str::uuid(), 'package_id' => $package->package_id,
            'teacher_level' => $teacher->teacher_level, 'grade_level' => 'SMP',
            'method' => 'online_zoom', 'learning_method' => 'online_zoom', 'category_rate' => 60000, 'base_rate' => 60000,
        ]);
        $admin = $this->makeAdmin();

        foreach ([0, -10000] as $amount) {
            $this->actingAs($admin)->putJson(route('admin.tariffs.update'), [
                'grade_level' => 'SMP', 'learning_method' => 'online_zoom', 'amount' => $amount, 'reason' => 'Nilai tidak valid',
            ])->assertUnprocessable()->assertJsonValidationErrors('amount');
        }

        $this->assertDatabaseHas('master_tariffs', ['package_id' => $package->package_id, 'category_rate' => 60000]);
        $this->assertDatabaseCount('tariff_audit_logs', 0);
    }

    public function test_admin_can_manage_dynamic_subjects_including_special_programs(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->from(route('admin.subjects'))->post(route('admin.subjects.store'), [
            'name' => 'Mengaji Metode Tilawati', 'grade_level' => 'UMUM', 'category' => 'Program Khusus',
        ])->assertRedirect(route('admin.subjects'));

        $subject = Subject::where('name', 'Mengaji Metode Tilawati')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.subjects.update', $subject->subject_id), [
            'name' => 'Mengaji Tilawati', 'grade_level' => 'UMUM', 'category' => 'Program Khusus',
        ])->assertRedirect();
        $subject->refresh();
        $this->assertSame('Mengaji Tilawati', $subject->name);
        [$tutorUser] = $this->makeTutor([], ['approved_levels' => ['UMUM']]);
        $this->actingAs($tutorUser)->get(route('tutor.classes.create'))->assertOk()->assertSee('Mengaji Tilawati');

        $this->actingAs($admin)->post(route('admin.subjects.toggle', $subject->subject_id))->assertRedirect();
        $this->assertFalse($subject->fresh()->is_active);
    }

    public function test_admin_can_view_finance_monitoring_and_confirm_tutor_payroll(): void
    {
        [$tutorUser, $teacher] = $this->makeTutor();
        [$student, $package, $order, $session] = $this->makeTutorSession($teacher);
        $order->update(['total_amount' => 200000, 'payment_status' => 'settled']);
        $workLog = TeacherWorkLog::create([
            'teacher_id' => $teacher->teacher_id, 'session_id' => $session->session_id,
            'duration_minutes' => 60, 'gross_session_amount' => 200000, 'teacher_earnings' => 50000,
            'payroll_status' => 'pending', 'created_at' => now(),
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get(route('admin.finance'))->assertOk()->assertSee('Rp 200.000')->assertSee('Rp 50.000');
        $this->postJson(route('admin.finance.payroll.paid', $workLog->log_id), ['reference' => 'TRANSFER-01'])->assertOk();
        $this->assertDatabaseHas('teacher_work_logs', ['log_id' => $workLog->log_id, 'payroll_status' => 'paid']);
        $this->assertDatabaseHas('teacher_payouts', ['teacher_id' => $teacher->teacher_id, 'amount' => 50000, 'payout_status' => 'paid']);

        $this->actingAs($tutorUser)->get(route('admin.finance'))->assertForbidden();
    }
}
