<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Kunjungan; // Model Kunjungan untuk antrian
use App\Models\PiketPetugas;
use App\Models\Tamu; // Model Tamu
use Illuminate\Support\Carbon;

class AntrianController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        
        // 1. Ambil ID Kunjungan yang SUDAH dilayani hari ini
        $kunjunganSudahDilayaniIds = Layanan::whereDate('created_at', $today)
                                         ->pluck('kunjungan_id')
                                         ->filter()
                                         ->unique();

        // 2. Ambil Kunjungan hari ini yang belum dilayani
        $antrian = Kunjungan::with('tamu') // Pastikan relasi 'tamu' ada di model Kunjungan
            ->whereDate('created_at', $today)
            // Filter: whereNotIn 'id' Kunjungan dari list Layanan yang sudah ada
            ->whereNotIn('id', $kunjunganSudahDilayaniIds) 
            ->get();
            
        // 3. Mapping dan Safety Check
        $formattedAntrian = $antrian->map(function ($kunjungan, $index) {
            // ✅ Null check untuk nama tamu
            $tamuName = $kunjungan->tamu->nama ?? 'Tamu Tidak Dikenal';

            return [
                'nama' => $tamuName, 
                'nomor' => $index + 1, 
            ];
        });

        return response()->json($formattedAntrian); // ✅ Pastikan selalu mengembalikan JSON
    }
}