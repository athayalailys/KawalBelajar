<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ProductionAdministratorSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_INITIAL_EMAIL');
        $name = (string) env('ADMIN_INITIAL_NAME', 'Administrator KawalBelajar');
        $password = (string) env('ADMIN_INITIAL_PASSWORD');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 16) {
            throw new RuntimeException('Set a valid ADMIN_INITIAL_EMAIL and an ADMIN_INITIAL_PASSWORD with at least 16 characters.');
        }

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'username' => Str::limit(Str::slug(Str::before($email, '@')), 50, ''),
                'full_name' => $name,
                'password_hash' => Hash::make($password),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
            ],
        );

        if ($user->role !== 'admin') {
            throw new RuntimeException('The configured initial administrator email already belongs to a non-admin account.');
        }

        Admin::query()->firstOrCreate(
            ['user_id' => $user->user_id],
            ['employee_id_number' => 'KB-ADMIN-INITIAL', 'position' => 'Administrator'],
        );
    }
}
