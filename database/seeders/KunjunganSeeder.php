<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_pengguna' => 1, // tamu id 1
                'timestamp_berkunjung' => Carbon::now()->subDays(2),
                'moda_kunjungan' => 'langsung',
                'tujuan_kunjungan' => 'Konsultasi data statistik',
                'foto' => null,
                'nomor_antrian' => 'A001',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_pengguna' => 2, // tamu id 2
                'timestamp_berkunjung' => Carbon::now()->subDay(),
                'moda_kunjungan' => 'online',
                'tujuan_kunjungan' => 'Permintaan data',
                'foto' => null,
                'nomor_antrian' => 'A002',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('kunjungan')->insert($data);
    }
}