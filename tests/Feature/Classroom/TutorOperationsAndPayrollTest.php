<?php

namespace Tests\Feature\Classroom;

use App\Models\DigitalReport;
use App\Models\StudentAttendance;
use App\Models\TeacherWorkLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\TutorTestFixtures;
use Tests\TestCase;

class TutorOperationsAndPayrollTest extends TestCase
{
    use RefreshDatabase;
    use TutorTestFixtures;

    public function test_nav_001_tutor_can_get_home_visit_navigation_and_other_tutor_is_forbidden(): void
    {
        [$ownerUser, $owner] = $this->makeTutor();
        [$otherTutor] = $this->makeTutor();
        [, , , $session] = $this->makeTutorSession($owner);

        $this->actingAs($ownerUser)->getJson(route('api.classroom.session.navigation', $session->session_id))
            ->assertOk()
            ->assertJsonPath('data.address', 'Jl. Uji No. 10')
            ->assertJsonPath('data.landmark', 'Dekat taman kota')
            ->assertJsonPath('data.google_maps_url', 'https://www.google.com/maps/dir/?api=1&destination=-3.316694,114.590111');

        $this->actingAs($otherTutor)->getJson(route('api.classroom.session.navigation', $session->session_id))
            ->assertForbidden();
    }

    public function test_att_001_clock_in_and_clock_out_persist_tutor_timestamps(): void
    {
        [$user, $teacher] = $this->makeTutor();
        [, , , $session] = $this->makeTutorSession($teacher);

        $this->actingAs($user)->postJson(route('api.classroom.attendance.clock-in'), ['session_id' => $session->session_id])
            ->assertOk();
        $this->postJson(route('api.classroom.attendance.clock-out'), ['session_id' => $session->session_id])
            ->assertOk();

        $this->assertNotNull($session->fresh()->absensi_masuk_guru);
        $this->assertNotNull($session->fresh()->absensi_keluar_guru);
    }

    public function test_eval_001_saves_student_attendance_posttest_notes_and_closes_session(): void
    {
        [$user, $teacher] = $this->makeTutor();
        [$student, , , $session] = $this->makeTutorSession($teacher);

        $this->actingAs($user)->post(route('tutor.sessions.evaluation.save', $session->session_id), [
            'students' => [
                $student->student_id => [
                    'attendance' => 'present',
                    'posttest_score' => 85,
                ],
            ],
            'teacher_notes' => 'Pembelajar memahami materi dengan baik.',
        ])->assertRedirect(route('tutor.class-details'));

        $this->assertDatabaseHas('student_attendances', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'status' => 'present',
        ]);
        $this->assertDatabaseHas('evaluation_scores', [
            'session_id' => $session->session_id,
            'student_id' => $student->student_id,
            'posttest_score' => 85,
            'teacher_notes' => 'Pembelajar memahami materi dengan baik.',
        ]);
        $this->assertDatabaseHas('learning_sessions', [
            'session_id' => $session->session_id,
            'session_status' => 'completed',
            'status' => 'completed',
        ]);
        $this->assertSame('published', DigitalReport::where('student_id', $student->student_id)->value('report_status'));
    }

    public function test_payroll_001_clock_out_creates_tutor_work_log_with_25_percent_share(): void
    {
        [$user, $teacher] = $this->makeTutor();
        [, , , $session] = $this->makeTutorSession($teacher);

        $this->actingAs($user)->postJson(route('api.classroom.attendance.clock-in'), ['session_id' => $session->session_id])->assertOk();
        $this->postJson(route('api.classroom.attendance.clock-out'), ['session_id' => $session->session_id])->assertOk();

        $this->assertDatabaseHas('teacher_work_logs', [
            'teacher_id' => $teacher->teacher_id,
            'session_id' => $session->session_id,
            'gross_session_amount' => 300000,
            'teacher_earnings' => 75000,
        ]);
        $this->assertSame(60, TeacherWorkLog::where('session_id', $session->session_id)->value('duration_minutes'));
    }
}
