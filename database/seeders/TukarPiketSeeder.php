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
        $petugasIds = DB::table('users')->pluck('id')->toArray();

        foreach (range(1, 10) as $i) {
            DB::table('tukar_piket')->insert([
                'petugas_asal_id' => $faker->randomElement($petugasIds),
                'petugas_pengganti_id' => $faker->randomElement($petugasIds),
                'tanggal' => $faker->dateTimeBetween('-2 months', '+1 month'),
                'alasan' => $faker->sentence,
                'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}