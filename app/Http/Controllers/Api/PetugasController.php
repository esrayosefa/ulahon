<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PiketPetugas;
use Illuminate\Support\Carbon;

class PetugasController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $piket = PiketPetugas::with('user') // Pastikan user_id match
            ->whereDate('tanggal', $today)
            ->get()
            ->map(function ($item) {
                // ✅ Null check untuk mencegah error 500
                $userName = $item->user->name ?? 'Petugas Tidak Dikenal';
                $userFoto = $item->user->foto ?? 'https://ui-avatars.com/api/?name=' . urlencode($userName);
                
                // Konversi shift string ke sesi integer untuk frontend Vue (sesi 1 atau 2)
                $sesi = ($item->shift === 'Pagi') ? 1 : 2;
                $jam = ($item->shift === 'Pagi') ? '08.00–12.00' : '13.00–16.00';

                return [
                    'nama' => $userName,
                    'jam' => $jam,
                    'foto' => $userFoto,
                    'sesi' => $sesi, // Mengirim sesi integer yang diharapkan Vue
                ];
            });

        return response()->json($piket);
    }
}