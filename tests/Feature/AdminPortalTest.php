<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LearningMaterial;
use App\Models\MasterTariff;
use App\Models\ModulePackage;
use App\Models\Order;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherLevelRequest;
use App\Models\TeacherWorkLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    private function makeAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        Admin::create(['user_id' => $user->user_id, 'position' => 'Administrator']);

        return $user;
    }

    public function test_admin_role_guard_and_login_redirect(): void
    {
        [$student] = $this->makeStudent();
        [$tutor] = $this->makeTutor();

        foreach ([$student, $tutor] as $user) {
            $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        }
        auth()->logout();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

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

    public function test_admin_can_approve_tutor_and_assign_verified_levels(): void
    {
        Mail::fake();
        [$user, $teacher] = $this->makeTutor(['is_active' => false], [
            'cv_status' => 'pending', 'approved_levels' => [], 'verified_levels' => [], 'requested_levels' => ['SMP'],
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.approve', $teacher->teacher_id), ['approved_levels' => ['SMP']])
            ->assertOk()->assertJsonPath('message', 'Tutor disetujui dan kredensial dikirim melalui email.');

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'approved']);
        $this->assertContains('SMP', $teacher->fresh()->verified_levels);
        $this->assertTrue($user->fresh()->is_active);
        Mail::assertSent(\App\Mail\TutorApprovedMail::class);
    }

    public function test_admin_rejection_requires_reason_and_saves_it(): void
    {
        Mail::fake();
        [, $teacher] = $this->makeTutor([], ['cv_status' => 'pending', 'requested_levels' => ['SMP']]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.reject', $teacher->teacher_id), [])->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->postJson(route('admin.tutors.reject', $teacher->teacher_id), ['reason' => 'Dokumen belum terbaca'])
            ->assertOk();

        $this->assertDatabaseHas('teachers', ['teacher_id' => $teacher->teacher_id, 'cv_status' => 'rejected', 'review_note' => 'Dokumen belum terbaca']);
        Mail::assertSent(\App\Mail\TutorRejectedMail::class);
    }

    public function test_suspending_tutor_removes_tutor_program_from_student_catalog(): void
    {
        [$tutorUser, $teacher] = $this->makeTutor();
        [$studentUser] = $this->makeStudent();
        $package = $this->makeTutorPackage($teacher, ['is_published' => true, 'is_open' => true]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.tutors.suspend', $teacher->teacher_id), ['reason' => 'Pelanggaran SOP'])->assertOk();
        $this->assertFalse($tutorUser->fresh()->is_active);

        $this->actingAs($studentUser)->getJson('/api/student/catalog?education_level=SMP&learning_method=online_zoom')
            ->assertOk()->assertJsonMissing(['package_id' => $package->package_id]);
    }

    public function test_admin_can_approve_tutor_level_upgrade(): void
    {
        Mail::fake();
        [$tutorUser, $teacher] = $this->makeTutor([], ['approved_levels' => ['SMP'], 'verified_levels' => ['SMP']]);
        $request = TeacherLevelRequest::create([
            'teacher_id' => $teacher->teacher_id,
            'requested_level' => 'SMA',
            'request_status' => 'pending_upgrade',
            'created_at' => now(),
        ]);
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->postJson(route('admin.level-requests.approve', $request->request_id))
            ->assertOk()->assertJsonPath('data.verified_levels.1', 'SMA');

        $this->assertContains('SMA', $teacher->fresh()->verified_levels);
        $this->assertDatabaseHas('teacher_level_requests', ['request_id' => $request->request_id, 'request_status' => 'approved']);
    }

    public function test_tariff_update_requires_positive_amount_and_records_audit(): void
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
        $this->assertDatabaseHas('tariff_audit_logs', ['admin_id' => $admin->admin->admin_id, 'old_price' => 50000, 'new_price' => 80000]);
    }

    public function test_admin_can_manage_subject_catalog_and_tutor_wizard_uses_active_subjects(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin)->from(route('admin.subjects'))->post(route('admin.subjects.store'), [
            'name' => 'Mengaji Metode Tilawati', 'grade_level' => 'SD', 'category' => 'Program Khusus',
        ])->assertRedirect(route('admin.subjects'));

        $subject = Subject::where('name', 'Mengaji Metode Tilawati')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.subjects.update', $subject->subject_id), [
            'name' => 'Mengaji Tilawati', 'grade_level' => 'SD', 'category' => 'Program Khusus',
        ])->assertRedirect();
        $subject->refresh();
        $this->assertSame('Mengaji Tilawati', $subject->name);
        [$tutorUser] = $this->makeTutor([], ['approved_levels' => ['SD']]);
        $this->actingAs($tutorUser)->get(route('tutor.classes.create'))->assertOk()->assertSee('Mengaji Tilawati');

        $this->actingAs($admin)->post(route('admin.subjects.toggle', $subject->subject_id))->assertRedirect();
        $this->assertFalse($subject->fresh()->is_active);
    }

    public function test_admin_finance_displays_revenue_allocations_and_can_mark_payroll_paid(): void
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
