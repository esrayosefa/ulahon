<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PiketPetugasSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_petugas' => 1, // user id
                'tanggal' => Carbon::now()->subDays(2)->toDateString(),
                'shift' => 'sesi 1',
                'jumlah_pengunjung' => 5,
                'kendala' => null,
                'dokumentasi' => null,
                'cek_in' => Carbon::now()->subDays(2)->setHour(8),
                'cek_out' => Carbon::now()->subDays(2)->setHour(16),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id_petugas' => 2,
                'tanggal' => Carbon::now()->subDay()->toDateString(),
                'shift' => 'sesi 2',
                'jumlah_pengunjung' => 3,
                'kendala' => 'Sistem antrian lambat',
                'dokumentasi' => null,
                'cek_in' => Carbon::now()->subDay()->setHour(8),
                'cek_out' => Carbon::now()->subDay()->setHour(16),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('piket_petugas')->insert($data);
    }
}