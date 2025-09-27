<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TamuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Budi Santoso',
                'no_whatsapp' => '081234567890',
                'email' => 'budi@example.com',
                'asal_instansi' => 'Dinas Pendidikan',
                'jenis_kelamin' => 'L',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Siti Aminah',
                'no_whatsapp' => '082345678901',
                'email' => 'siti@example.com',
                'asal_instansi' => 'Universitas Sumatera Utara',
                'jenis_kelamin' => 'P',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'nama' => 'Andi Saputra',
                'no_whatsapp' => '083456789012',
                'email' => 'andi@example.com',
                'asal_instansi' => 'Badan Keuangan Daerah',
                'jenis_kelamin' => 'L',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        DB::table('tamu')->insert($data);
    }
}