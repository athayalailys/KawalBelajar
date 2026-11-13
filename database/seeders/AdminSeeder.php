<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@kawalbelajar.com'],
            [
                'full_name' => 'Super Admin',
                'password_hash' => Hash::make('password123'),
                'is_active' => true,
                'role' => 'admin', 
            ]
        );

        Admin::updateOrCreate(
            ['user_id' => $user->user_id],
            ['employee_id_number' => 'KB-ADMIN-001', 'position' => 'Administrator']
        );
    }
}
