<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $kunjunganIds = DB::table('kunjungan')->pluck('id')->toArray();
        $petugasIds = DB::table('users')->pluck('id')->toArray();

        foreach (range(1, 30) as $i) {
            DB::table('layanan')->insert([
                'kunjungan_id' => $faker->randomElement($kunjunganIds),
                'petugas_id' => $faker->randomElement($petugasIds),
                'jenis_layanan' => $faker->randomElement(['Konsultasi Statistik', 'Permintaan Data', 'Lainnya']),
                'deskripsi' => $faker->paragraph,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}