<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seed akun demo dan data saling-terhubung untuk pengujian browser. */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            EcosystemDemoSeeder::class,
        ]);
    }
}
