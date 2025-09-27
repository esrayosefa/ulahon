<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            TamuSeeder::class,
            KunjunganSeeder::class,
            PiketPetugasSeeder::class,
            LayananSeeder::class,
            TukarPiketSeeder::class,
            PengaduanSeeder::class,
        ]);
    }
}