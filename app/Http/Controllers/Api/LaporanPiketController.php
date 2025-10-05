<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanPiket;
use App\Models\PiketPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanPiketController extends Controller
{
    /** mapping jam sesi */
    private array $sessionTimes = [
        1 => ['start' => '08:00:00', 'end' => '12:00:00'],
        2 => ['start' => '12:00:00', 'end' => '16:00:00'],
    ];

    /** GET /api/piket/report/today */
    public function today(Request $request)
    {
        $user = $request->user();

        $piket = PiketPetugas::with('petugas:id,name,foto')
            ->whereDate('tanggal', today())
            ->where('petugas_id', $user->id)
            ->first();

        if (!$piket) {
            return response()->json([
                'piket' => null,
                'report' => null,
                'session_times' => $this->sessionTimes,
                'now' => now(),
            ]);
        }

        $report = LaporanPiket::where('id_piket', $piket->id_piket)->first();

        return response()->json([
            'piket' => $piket,
            'report' => $report,
            'session_times' => $this->sessionTimes,
            'now' => now(),
        ]);
    }

    /** POST /api/piket/report/start  body: { id_piket } */
    public function start(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'id_piket' => ['required','exists:piket,id_piket'],
        ]);

        $piket = PiketPetugas::where('id_piket', $data['id_piket'])->firstOrFail();

        if ((int)$piket->petugas_id !== (int)$user->id) {
            return response()->json(['message' => 'Bukan jadwal Anda'], 403);
        }

        // cek report existing
        $report = LaporanPiket::firstOrNew(['id_piket' => $piket->id_piket]);
        $report->petugas_id = $user->id;
        if (!$report->mulai_at) {
            $report->mulai_at = now();
        }

        // hitung status kehadiran saat mulai
        $startPlan = Carbon::parse($piket->tanggal.' '.$this->sessionTimes[$piket->sesi]['start']);
        $diffMin   = $startPlan->diffInMinutes($report->mulai_at, false); // bisa negatif

        if     ($diffMin <= 5)  $status = 'tepat_waktu';
        elseif ($diffMin <= 30) $status = 'terlambat_a';
        else                    $status = 'terlambat_b';

        $report->status_kehadiran = $status;
        $report->save();

        return response()->json(['message' => 'Sesi dimulai', 'data' => $report->fresh()]);
    }

    /** POST /api/piket/report/finish  body: { id_piket, catatan? } */
    public function finish(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'id_piket' => ['required','exists:piket,id_piket'],
            'catatan'  => ['nullable','string'],
        ]);

        $piket = PiketPetugas::where('id_piket', $data['id_piket'])->firstOrFail();
        if ((int)$piket->petugas_id !== (int)$user->id) {
            return response()->json(['message' => 'Bukan jadwal Anda'], 403);
        }

        /** @var LaporanPiket $report */
        $report = LaporanPiket::firstOrCreate(
            ['id_piket' => $piket->id_piket],
            ['petugas_id' => $user->id, 'mulai_at' => now()]
        );

        $report->selesai_at = now();
        if ($request->filled('catatan')) $report->catatan = $data['catatan'];
        $report->save();

        return response()->json(['message' => 'Sesi diakhiri', 'data' => $report->fresh()]);
    }

    /** GET /api/piket/report/history?search=&per_page=15 */
    public function history(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 15);
        $search  = (string) $request->query('search', '');

        $q = LaporanPiket::query()
            ->with([
                'petugas:id,name,foto',
                'piket:id_piket,tanggal,sesi,petugas_id'
            ])
            ->join('piket', 'piket.id_piket', '=', 'laporan_piket.id_piket')
            ->select('laporan_piket.*', 'piket.tanggal', 'piket.sesi');

        if ($search !== '') {
            $q->where(function($qq) use ($search) {
                $qq->whereHas('petugas', fn($uu) => $uu->where('name', 'like', "%$search%"))
                   ->orWhere('piket.tanggal', 'like', "%$search%")
                   ->orWhere('piket.sesi', 'like', "%$search%")
                   ->orWhere('laporan_piket.status_kehadiran', 'like', "%$search%");
            });
        }

        $q->orderByDesc('laporan_piket.created_at');

        return $q->paginate($perPage);
    }
}
