<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class KunjunganSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $tamuIds = DB::table('tamu')->pluck('id')->toArray();

        foreach (range(1, 30) as $i) {
            DB::table('kunjungan')->insert([
                'tamu_id' => $faker->randomElement($tamuIds),
                'tanggal_kunjungan' => $faker->dateTimeBetween('-1 year', 'now'),
                'keperluan' => $faker->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
