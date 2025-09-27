<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TukarPiketSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_piket_awal' => 1,
                'id_piket_tukar' => 2,
                'tanggal_awal' => Carbon::now()->subDays(2)->toDateString(),
                'tanggal_tukar' => Carbon::now()->subDay()->toDateString(),
                'sesi_awal' => 'sesi 1',
                'sesi_tukar' => 'sesi 2',
                'petugas_tukar' => 1,
                'alasan' => 'Ada rapat mendadak',
                'status' => 'disetujui',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('tukar_piket')->insert($data);
    }
}
