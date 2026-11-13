<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@kawalbelajar.com'],
            [
                'full_name' => 'Super Admin',
                'password_hash' => Hash::make('password123'),
                'is_active' => true,
                'role' => 'admin', 
            ]
        );
    }
}