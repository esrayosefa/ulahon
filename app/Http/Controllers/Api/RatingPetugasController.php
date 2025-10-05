<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Penilaian;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RatingPetugasController extends Controller
{
    /**
     * POST /api/layanan/{id}/feedback-link
     * Membuat (atau mengambil) token penilaian untuk layanan ini, lalu
     * mengembalikan URL yang bisa kamu kirim via WhatsApp/SMS/email.
     */
    public function makeLink(Request $request, string $layananId)
    {
        $layanan = Layanan::with(['petugas:id,name', 'kunjungan.tamu:id,nama'])->findOrFail($layananId);

        /** satu layanan = satu undangan penilaian */
        $pen = Penilaian::firstOrCreate(
            ['layanan_id' => $layanan->id],
            [
                'tamu_id'    => optional($layanan->kunjungan)->tamu_id ?? null,
                'petugas_id' => $layanan->petugas_id,
                'token'      => Str::random(48),
            ]
        );

        $url = url("/feedback/penilaian/{$pen->token}"); // mis. akan kamu buka di halaman publik

        return response()->json([
            'message' => 'Link penilaian disiapkan',
            'url' => $url,
            'token' => $pen->token,
        ]);
    }

    /**
     * GET /api/feedback/ratings/summary?search=&per_page=12
     * Ringkasan rating per petugas: avg, count, komentar terakhir.
     */
    public function summary(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 12);
        $search  = trim((string) $request->query('search', ''));

        // agregasi avg & count per petugas
        $agg = Penilaian::whereNotNull('nilai')
            ->select('petugas_id',
                DB::raw('ROUND(AVG(nilai),2) as avg_nilai'),
                DB::raw('COUNT(*) as total_ulasan')
            )
            ->groupBy('petugas_id');

        if ($search !== '') {
            // filter by nama petugas
            $agg->whereIn('petugas_id', function($q) use ($search) {
                $q->select('id')->from('users')->where('name','like',"%{$search}%");
            });
        }

        $paginator = $agg->orderByDesc('avg_nilai')->paginate($perPage);

        // ambil identitas petugas + komentar terakhir untuk item halaman ini saja
        $ids = collect($paginator->items())->pluck('petugas_id')->all();
        $petugas = User::whereIn('id', $ids)->get(['id','name','foto','no_hp','phone','telepon'])->keyBy('id');

        $latest = Penilaian::whereIn('petugas_id', $ids)
            ->whereNotNull('nilai')
            ->latest('id')
            ->get()
            ->groupBy('petugas_id')
            ->map(fn($g) => optional($g->first())->komentar);

        // rakit respons
        $items = array_map(function($row) use ($petugas, $latest) {
            $p = $petugas[$row['petugas_id']] ?? null;
            return [
                'petugas_id'  => $row['petugas_id'],
                'avg_nilai'   => (float) $row['avg_nilai'],
                'total_ulasan'=> (int) $row['total_ulasan'],
                'komentar'    => $latest[$row['petugas_id']] ?? null,
                'petugas'     => [
                    'id'   => $p?->id,
                    'name' => $p?->name,
                    'foto' => $p?->foto,
                    // coba beberapa kemungkinan kolom hp; yang ada duluan dipakai
                    'hp'   => $p?->no_hp ?? $p?->phone ?? $p?->telepon ?? null,
                ],
            ];
        }, $paginator->items());

        return response()->json([
            'data'         => $items,
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ]);
    }

    /**
     * (Opsional) POST /api/feedback/ratings/{token}
     * endpoint publik untuk menyimpan hasil rating yang dikirim tamu.
     */
    public function submitByToken(Request $request, string $token)
    {
        $data = $request->validate([
            'nilai'    => ['required','integer','min:1','max:5'],
            'komentar' => ['nullable','string'],
        ]);

        $pen = Penilaian::where('token', $token)->firstOrFail();
        $pen->nilai = $data['nilai'];
        $pen->komentar = $data['komentar'] ?? null;
        $pen->submitted_at = now();
        $pen->save();

        return response()->json(['message' => 'Terima kasih atas penilaian Anda.']);
    }
}
