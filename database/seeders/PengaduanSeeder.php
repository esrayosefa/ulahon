<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PengaduanSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $layananIds = DB::table('layanan')->pluck('id')->toArray();

        if (empty($layananIds)) {
            $this->command->warn("⚠️ Tidak ada data layanan, seeding pengaduan dilewati.");
            return;
        }

        foreach (range(1, 20) as $i) {
            DB::table('pengaduan')->insert([
                'layanan_id' => $faker->randomElement($layananIds),
                'tanggal' => $faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
                'jenis_aduan' => $faker->randomElement(['Pelayanan', 'Teknis', 'Sarana', 'Lainnya']),
                'identitas_pelapor' => $faker->name(),
                'isi_pengaduan' => $faker->sentence(12),
                'pic_tindak_lanjut' => $faker->name(),
                'status' => $faker->randomElement(['open', 'in_progress', 'resolved']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $this->command->info("✅ Seeder pengaduan berhasil dimasukkan (20 data contoh).");
    }
}
