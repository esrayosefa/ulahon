<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PiketPetugas;
use Illuminate\Support\Carbon;

class PetugasController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        $data = PiketPetugas::with('user')
            ->where('tanggal', $today)
            ->get()
            ->groupBy('sesi')
            ->map(function ($group, $sesi) {
                $jam = $sesi == 1 ? '08.00–12.00' : '13.00–16.00';
                return $group->map(fn($p) => [
                    'name' => $p->user->name,
                    'foto' => $p->user->foto,
                    'jam' => $jam,
                ]);
            });

        return response()->json([
            'sesi_1' => $data[1] ?? [],
            'sesi_2' => $data[2] ?? [],
        ]);
    }
}