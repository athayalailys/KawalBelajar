<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /** Seed akun demo dan data saling-terhubung untuk pengujian browser. */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call(ProductionAdministratorSeeder::class);

            return;
        }

        $this->call([
            AdminSeeder::class,
            EcosystemDemoSeeder::class,
        ]);
    }
}
