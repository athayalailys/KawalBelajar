<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\TeacherWorkLog;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class TutorPortalPagesTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    public function test_active_tutor_can_open_available_portal_pages(): void
    {
        [$user] = $this->makeTutor();

        $this->actingAs($user)->get('/tutor/dashboard')->assertOk();
        $this->get('/tutor/classes')->assertOk();
        $this->get('/tutor/classes/details')->assertOk();
        $this->get('/tutor/availability')->assertOk();
        $this->get('/tutor/teaching-room')->assertOk();
    }

    public function test_student_is_forbidden_from_tutor_portal(): void
    {
        [$student] = $this->makeStudent();

        $this->actingAs($student)->get('/tutor/dashboard')->assertForbidden();
        $this->get('/tutor/classes')->assertForbidden();
        $this->get('/tutor/availability')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_tutor_portal(): void
    {
        $this->get('/tutor/dashboard')->assertRedirect(route('login'));
        $this->get('/tutor/classes')->assertRedirect(route('login'));
        $this->get('/tutor/availability')->assertRedirect(route('login'));
    }

    public function test_payroll_page_shows_tutors_own_25_percent_work_log_and_denies_other_roles(): void
    {
        [$tutorUser, $teacher] = $this->makeTutor();
        [, , , $session] = $this->makeTutorSession($teacher);
        TeacherWorkLog::create([
            'teacher_id' => $teacher->teacher_id,
            'session_id' => $session->session_id,
            'duration_minutes' => 60,
            'gross_session_amount' => 300000,
            'teacher_earnings' => 75000,
            'payroll_status' => 'pending',
            'created_at' => now(),
        ]);

        $this->actingAs($tutorUser)->get(route('tutor.payroll'))
            ->assertOk()
            ->assertSee('Rp 75.000')
            ->assertSee('25% dari total kas sesi');

        [$studentUser] = $this->makeStudent();
        $this->actingAs($studentUser)->get(route('tutor.payroll'))->assertForbidden();
        auth()->logout();
        $this->get(route('tutor.payroll'))->assertRedirect(route('login'));
    }
}
