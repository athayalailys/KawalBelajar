<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TutorDummySeeder extends Seeder
{
    public function run(): void
    {
        $tutors = [
            [
                'full_name' => 'M. Haikal Fauzan, S.Pd',
                'email' => 'haikal@kawalbelajar.id',
                'phone' => '081234567891',
            ],
            [
                'full_name' => 'Sarah Amanda, M.Si',
                'email' => 'sarah@kawalbelajar.id',
                'phone' => '081234567892',
            ],
            [
                'full_name' => 'Dimas Arya, S.Si',
                'email' => 'dimas@kawalbelajar.id',
                'phone' => '081234567893',
            ]
        ];

        foreach ($tutors as $data) {
            $userId = (string) Str::uuid();

            // 1. Buat Akun User
            User::create([
                'user_id' => $userId,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone'],
                'password_hash' => Hash::make('password123'),
                'role' => 'tutor',
                'is_active' => true,
            ]);

            // 2. Buat Profil Teacher
            Teacher::create([
                'teacher_id' => (string) Str::uuid(),
                'user_id' => $userId,
            ]);
        }
    }
}