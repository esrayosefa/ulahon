<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatistikController extends Controller
{
    public function mingguan()
    {
        $today = now();
        $start = $today->copy()->startOfWeek();
        $end = $today->copy()->endOfWeek();

        $data = DB::table('layanan')
            ->selectRaw('DATE(created_at) as tanggal, COUNT(*) as jumlah')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        $labels = [];
        $jumlah = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $tanggal = $date->toDateString();
            $labels[] = $date->translatedFormat('D');
            $match = $data->firstWhere('tanggal', $tanggal);
            $jumlah[] = $match ? $match->jumlah : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $jumlah,
        ]);
    }

    public function bulanan()
    {
        $data = DB::table('layanan')
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as jumlah')
            ->whereYear('created_at', now()->year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $labels = [];
        $jumlah = [];

        foreach (range(1, 12) as $bulan) {
            $labels[] = Carbon::create()->month($bulan)->translatedFormat('M');
            $match = $data->firstWhere('bulan', $bulan);
            $jumlah[] = $match ? $match->jumlah : 0;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $jumlah,
        ]);
    }
}
