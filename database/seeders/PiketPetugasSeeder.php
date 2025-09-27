<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\User;

class PiketPetugasSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $faker = Faker::create('id_ID');

        $tanggalAwal = Carbon::now()->startOfWeek();
        $sesi = 1;

        foreach ($users as $user) {
            DB::table('piket_petugas')->insert([
                'petugas_id' => $user->id,
                'tanggal' => $tanggalAwal->copy()->addDays(rand(0, 14)),
                'sesi' => $sesi,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // toggle sesi 1 → 2, 2 → 1
            $sesi = $sesi === 1 ? 2 : 1;
        }
    }
}