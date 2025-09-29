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

        $piket = PiketPetugas::with('user')
            ->whereDate('tanggal', $today)
            ->get()
            ->map(function ($item) {
                return [
                    'nama' => $item->user->name,
                    'jam' => $item->sesi == 1 ? '08.00–12.00' : '13.00–16.00',
                    'foto' => $item->user->foto ?? 'https://ui-avatars.com/api/?name=' . urlencode($item->user->name),
                    'sesi' => $item->sesi,
                ];
            });

        return response()->json($piket);
    }
}