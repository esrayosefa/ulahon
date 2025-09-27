<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\User;

class TamuSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        foreach (range(1, 20) as $i) {
            DB::table('tamu')->insert([
                'nama' => $faker->name,
                'instansi' => $faker->company,
                'alamat' => $faker->address,
                'no_hp' => $faker->phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}