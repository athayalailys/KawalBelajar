<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentAuthAndParentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_reg_001_registers_sd_student_with_parent_pin(): void
    {
        $response = $this->postJson('/api/student/register', $this->registrationPayload([
            'education_level' => 'SD',
            'parent_name' => 'Orang Tua',
            'parent_phone' => '081234567890',
            'parent_pin' => '123456',
        ]));

        $response->assertCreated()->assertJsonPath('data.parent_mode_visible', true);
        $user = User::where('email', 'anak@example.test')->firstOrFail();
        $student = Student::where('user_id', $user->user_id)->firstOrFail();
        $this->assertSame('Orang Tua', $student->parent_name);
        $this->assertTrue(Hash::check('123456', $student->parent_pin_hash));
    }

    public function test_student_reg_002_registers_independent_smp_student(): void
    {
        $this->postJson('/api/student/register', $this->registrationPayload(['education_level' => 'SMP']))
            ->assertCreated()
            ->assertJsonPath('data.parent_mode_visible', true);

        $user = User::where('email', 'anak@example.test')->firstOrFail();
        $this->assertDatabaseHas('students', ['user_id' => $user->user_id, 'education_level' => 'SMP']);
    }

    public function test_parent_menu_visibility_matches_education_level(): void
    {
        foreach (['SD', 'SMP', 'SMA'] as $level) {
            $payload = [
                'email' => strtolower($level) . '@example.test',
                'education_level' => $level,
            ];
            if ($level === 'SD') {
                $payload += ['parent_name' => 'Orang Tua', 'parent_phone' => '081234567890', 'parent_pin' => '123456'];
            }
            $this->postJson('/api/student/register', $this->registrationPayload($payload))
                ->assertCreated()->assertJsonPath('data.parent_mode_visible', true);
        }

        $this->postJson('/api/student/register', $this->registrationPayload([
            'email' => 'umum@example.test',
            'education_level' => 'UMUM',
        ]))->assertCreated()->assertJsonPath('data.parent_mode_visible', false);
    }

    public function test_pin_001_correct_pin_opens_parent_mode_progress(): void
    {
        $studentUser = $this->createStudentWithPin();

        $this->actingAs($studentUser)->postJson('/api/student/parent-mode/verify-pin', ['pin' => '123456'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.student_name', $studentUser->full_name);
    }

    public function test_pin_002_wrong_pin_is_rejected(): void
    {
        $studentUser = $this->createStudentWithPin();

        $this->actingAs($studentUser)->postJson('/api/student/parent-mode/verify-pin', ['pin' => '654321'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('pin');
    }

    private function createStudentWithPin(): User
    {
        $user = User::factory()->create(['role' => 'student']);
        Student::create([
            'user_id' => $user->user_id,
            'education_level' => 'SMP',
            'parent_pin_hash' => Hash::make('123456'),
        ]);

        return $user;
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Anak Contoh',
            'email' => 'anak@example.test',
            'phone_number' => '08123456789',
            'password' => 'password123',
            'education_level' => 'SMP',
        ], $overrides);
    }
}
