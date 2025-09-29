<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\PiketPetugas;
use Illuminate\Support\Carbon;

class AntrianController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $kunjungan = Layanan::with('tamu')
            ->whereDate('created_at', $today)
            ->get()
            ->map(function ($layanan, $index) {
                return [
                    'nama' => $layanan->keperluan,
                    'nomor' => $index + 1,
                ];
            });

        return response()->json($kunjungan);
    }
}