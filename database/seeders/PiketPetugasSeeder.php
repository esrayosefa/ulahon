<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;

class PiketPetugasSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = User::all()->pluck('id')->toArray();
        $jumlahPetugas = count($petugas);

        if ($jumlahPetugas < 2) {
            $this->command->warn("⚠️ Minimal butuh 2 petugas untuk jadwal piket!");
            return;
        }

        $startDate = Carbon::today();
        $endDate   = Carbon::createFromDate(now()->year, 12, 30);
        $periode   = CarbonPeriod::create($startDate, $endDate);

        $insertData = [];
        $lastPair = [];

        foreach ($periode as $tanggal) {
            if ($tanggal->isWeekday()) {

                // ambil random 2 petugas yang berbeda
                do {
                    $pair = collect($petugas)->random(2)->values()->toArray();
                } while ($pair === $lastPair); // hindari pasangan sama berturut-turut

                $lastPair = $pair;

                // shift pagi
                $insertData[] = [
                    'id_petugas'       => $pair[0],
                    'tanggal'          => $tanggal->toDateString(),
                    'shift'            => 'Pagi',
                    'jumlah_pengunjung'=> 0,
                    'kendala'          => null,
                    'dokumentasi'      => null,
                    'cek_in'           => null,
                    'cek_out'          => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];

                // shift siang
                $insertData[] = [
                    'id_petugas'       => $pair[1],
                    'tanggal'          => $tanggal->toDateString(),
                    'shift'            => 'Siang',
                    'jumlah_pengunjung'=> 0,
                    'kendala'          => null,
                    'dokumentasi'      => null,
                    'cek_in'           => null,
                    'cek_out'          => null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
        }

        DB::table('piket')->insert($insertData);

        $this->command->info("✅ Jadwal piket berhasil dibuat dengan rotasi pasangan dari {$startDate->toDateString()} sampai {$endDate->toDateString()}");
    }
}
