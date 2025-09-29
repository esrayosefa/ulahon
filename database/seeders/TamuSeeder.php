<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class TamuSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $genders = ['L','P'];

        $rows = [];
        for ($i = 0; $i < 80; $i++) {
            $rows[] = [
                'nama' => $faker->name,
                'no_hp' => $faker->phoneNumber,
                'email' => $faker->safeEmail,
                'asal_instansi' => $faker->company,
                'jenis_kelamin' => $faker->randomElement($genders),
                'waktu_kunjungan' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'alamat' => $faker->address,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('tamu')->insert($rows);
    }
}