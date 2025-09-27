<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_kunjungan' => 1,
                'id_petugas' => 1, // users id 1
                'id_piket' => 1,
                'jenis_layanan' => 'Konsultasi Statistik',
                'foto' => null,
                'nomor_antrian' => 'A001',
                'timestamp_dilayani' => Carbon::now()->subDays(2),
                'tanggal_dilayani' => Carbon::now()->subDays(2)->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_kunjungan' => 2,
                'id_petugas' => 2,
                'id_piket' => 2,
                'jenis_layanan' => 'Permintaan Data',
                'foto' => null,
                'nomor_antrian' => 'A002',
                'timestamp_dilayani' => Carbon::now()->subDay(),
                'tanggal_dilayani' => Carbon::now()->subDay()->toDateString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('layanan')->insert($data);
    }
}
