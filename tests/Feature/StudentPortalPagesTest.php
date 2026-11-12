<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_open_all_student_portal_pages(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        Student::create([
            'user_id' => $user->user_id,
            'education_level' => 'SMP',
        ]);

        $this->actingAs($user)->withoutVite();

        foreach ([
            'student.dashboard',
            'student.catalog',
            'student.booking',
            'student.schedule',
            'student.classroom',
            'student.report',
            'student.parent',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_guest_is_redirected_to_login_from_student_portal(): void
    {
        $this->get(route('student.catalog'))->assertRedirect(route('login'));
    }
}
