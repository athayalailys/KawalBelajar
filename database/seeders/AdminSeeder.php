<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'admin@kawalbelajar.id'],
            [
                'username' => 'admin',
                'full_name' => 'Admin KawalBelajar',
                'password_hash' => Hash::make('password'),
                'is_active' => true,
                'role' => 'admin',
            ],
        );

        Admin::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            ['employee_id_number' => 'KB-ADMIN-001', 'position' => 'Administrator'],
        );
    }
}
