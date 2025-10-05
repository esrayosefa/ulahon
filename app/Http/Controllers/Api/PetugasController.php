<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PiketPetugas;
use App\Models\TukarPiket;

class PetugasController extends Controller
{
    /* ===========================
       Tetap: Overview mingguan
    =========================== */
    public function piketOverview(Request $request)
    {
        $user = $request->user();

        $start = $request->query('start');
        $start = $start ? Carbon::parse($start) : today();

        $monday = (clone $start)->startOfWeek(Carbon::MONDAY);
        $sunday = (clone $monday)->endOfWeek(Carbon::SUNDAY);

        // minggu ini
        $weekly = PiketPetugas::with('petugas:id,name,foto')
            ->whereBetween('tanggal', [$monday->toDateString(), $sunday->toDateString()])
            ->orderBy('tanggal')->orderBy('sesi')
            ->get();

        // hari ini
        $today = PiketPetugas::with('petugas:id,name,foto')
            ->whereDate('tanggal', today())
            ->get()->keyBy('sesi');

        // jadwal saya
        $mine = PiketPetugas::with('petugas:id,name,foto')
            ->where('petugas_id', $user->id)
            ->whereBetween('tanggal', [$monday->toDateString(), $sunday->toDateString()])
            ->orderBy('tanggal')->orderBy('sesi')
            ->get();

        return response()->json([
            'session_times' => [1=>'08.00–12.00', 2=>'12.00–16.00'],
            'today' => ['1'=>$today->get(1), '2'=>$today->get(2)],
            'week'  => $weekly,
            'mine'  => $mine,
        ]);
    }

    /* =====================================
       CREATE pengajuan tukar (sesuai DB kamu)
       Body:
       - id_piket_awal* (piket milik user)
       - id_piket_tukar* (piket target pengganti)
       - alasan (opsional)
    ===================================== */
    public function storeSwap(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'id_piket_awal'  => ['required','exists:piket,id_piket'],
            'id_piket_tukar' => ['required','exists:piket,id_piket','different:id_piket_awal'],
            'alasan'         => ['nullable','string'],
        ]);

        // pastikan piket awal memang milik pemohon
        $awal = PiketPetugas::where('id_piket', $data['id_piket_awal'])->firstOrFail();
        if ((int)$awal->petugas_id !== (int)$user->id) {
            return response()->json(['message' => 'Anda hanya bisa menukar piket milik Anda.'], 403);
        }

        $tukar = PiketPetugas::where('id_piket', $data['id_piket_tukar'])->firstOrFail();

        // Simpan dengan menyalin tanggal/sesi ke tabel tukar_piket
        $swap = TukarPiket::create([
            'id_piket_awal'  => $awal->id_piket,
            'id_piket_tukar' => $tukar->id_piket,
            'tanggal_awal'   => $awal->tanggal,
            'tanggal_tukar'  => $tukar->tanggal,
            'sesi_awal'      => $awal->sesi,   // ganti jika kolom namanya berbeda
            'sesi_tukar'     => $tukar->sesi,  // ganti jika kolom namanya berbeda
            'petugas_tukar'  => $user->id,
            'alasan'         => $data['alasan'] ?? null,
            'status'         => 'pending',
        ])->load(['awal.petugas:id,name','tukar.petugas:id,name','pemohon:id,name']);

        return response()->json(['message'=>'Pengajuan tukar dikirim','data'=>$swap], 201);
    }

    /* =====================================
       ADMIN: daftar pengajuan (default pending)
    ===================================== */
    public function listSwapRequests(Request $request)
    {
        $user = $request->user();
        if (($user->role ?? null) !== 'admin') abort(403, 'Hanya admin');

        $status  = $request->query('status', 'pending');
        $perPage = (int) $request->integer('per_page', 10);

        $q = TukarPiket::with([
            'awal.petugas:id,name',
            'tukar.petugas:id,name',
            'pemohon:id,name',
        ])->orderByDesc('created_at');

        if (in_array($status, ['pending','disetujui','ditolak'], true)) {
            $q->where('status', $status);
        }

        return $q->paginate($perPage);
    }

    /* =====================================
       ADMIN: putuskan pengajuan
       Body: { aksi: 'approve'|'reject' }
       Approve => tukar petugas_id antar 2 baris piket.
    ===================================== */
    public function decideSwapRequest(Request $request, string $id)
    {
        $user = $request->user();
        if (($user->role ?? null) !== 'admin') abort(403, 'Hanya admin');

        $data = $request->validate([
            'aksi' => ['required','in:approve,reject'],
        ]);

        $swap = TukarPiket::with(['awal','tukar'])->findOrFail($id);
        if ($swap->status !== 'pending') {
            return response()->json(['message' => 'Pengajuan sudah diputuskan.'], 422);
        }

        if ($data['aksi'] === 'approve') {
            // Tukar petugas_id antar jadwal
            DB::transaction(function () use ($swap) {
                $awal  = $swap->awal;   // PiketPetugas
                $tukar = $swap->tukar;  // PiketPetugas

                // asumsikan kolomnya 'petugas_id'
                $tmp = $awal->petugas_id;
                $awal->update(['petugas_id' => $tukar->petugas_id]);
                $tukar->update(['petugas_id' => $tmp]);

                $swap->update(['status' => 'disetujui']);
            });
        } else {
            $swap->update(['status' => 'ditolak']);
        }

        return response()->json(['message' => 'Keputusan tersimpan', 'data' => $swap->fresh()->load(['awal.petugas','tukar.petugas','pemohon'])]);
    }
}
