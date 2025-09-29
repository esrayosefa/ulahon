<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TukarPiketSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ambil data piket & users dari DB
        $piketIds   = DB::table('piket')->pluck('id_piket')->toArray();
        $petugasIds = DB::table('users')->pluck('id')->toArray();

        if (count($piketIds) < 2 || empty($petugasIds)) {
            $this->command->warn("⚠️ Tidak cukup data untuk seeding tukar_piket (butuh minimal 2 piket & 1 user).");
            return;
        }

        foreach (range(1, 10) as $i) {
            // ambil 2 piket berbeda untuk ditukar
            $piketAwal = $faker->randomElement($piketIds);
            $piketTukar = $faker->randomElement(array_diff($piketIds, [$piketAwal]));

            DB::table('tukar_piket')->insert([
                'id_piket_awal' => $piketAwal,
                'id_piket_tukar' => $piketTukar,
                'tanggal_awal' => $faker->dateTimeBetween('-1 month', 'now'),
                'tanggal_tukar' => $faker->dateTimeBetween('now', '+1 month'),
                'sesi_awal' => $faker->randomElement(['Pagi', 'Siang']),
                'sesi_tukar' => $faker->randomElement(['Pagi', 'Siang']),
                'petugas_tukar' => $faker->randomElement($petugasIds),
                'alasan' => $faker->sentence(),
                'status' => $faker->randomElement(['pending', 'disetujui', 'ditolak']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("✅ Seeder tukar_piket berhasil dibuat (10 data contoh).");
    }
}
