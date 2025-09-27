<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengaduanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'id_pengguna' => 1,
                'tanggal' => Carbon::now()->subDays(1)->toDateString(),
                'identitas_pelapor' => 'Budi Santoso',
                'jenis_aduan' => 'Pelayanan kurang cepat',
                'deskripsi_aduan' => 'Waktu tunggu terlalu lama',
                'pic_tindak_lanjut' => 1,
                'tindak_lanjut' => 'Mengevaluasi antrian layanan',
                'bukti_tindak_lanjut' => null,
                'status_penyelesaian' => 'proses',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('pengaduan')->insert($data);
    }
}
