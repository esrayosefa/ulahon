<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class KunjunganController extends Controller
{
    /**
     * GET /api/kunjungan
     * Params:
     *  - view: 'today' | 'history' (default 'today')
     *  - search: string
     *  - per_page: int (default 10)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);
        $view    = $request->query('view', 'today');
        $search  = trim((string) $request->query('search', ''));

        $tz   = 'Asia/Jakarta';
        $today = Carbon::now($tz)->startOfDay();

        $q = Kunjungan::query()
            ->with([
                'tamu:id,nama,no_hp,asal_instansi',
                'layanan:id,kunjungan_id,jenis_layanan,petugas_id,created_at,updated_at',
                'petugas:id,name'
            ]);

        // Filter tab
        if ($view === 'today') {
            $q->whereDate('tanggal_kunjungan', $today->toDateString());
        } elseif ($view === 'history') {
            $q->whereDate('tanggal_kunjungan', '<', $today->toDateString());
        }

        // Search (nama tamu / no_hp / instansi / jenis layanan / keperluan)
        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->whereHas('tamu', function ($t) use ($search) {
                        $t->where('nama', 'like', "%{$search}%")
                          ->orWhere('no_hp', 'like', "%{$search}%")
                          ->orWhere('asal_instansi', 'like', "%{$search}%");
                    })
                   ->orWhereHas('layanan', function ($l) use ($search) {
                        $l->where('jenis_layanan', 'like', "%{$search}%");
                    })
                   ->orWhere('keperluan', 'like', "%{$search}%");
            });
        }

        $paginator = $q->orderByDesc('tanggal_kunjungan')
                       ->orderByDesc('id')
                       ->paginate($perPage);

        // return default Laravel paginator JSON (data, links, meta, etc.)
        return response()->json($paginator);
    }

    /**
     * POST /api/kunjungan
     * Body:
     *  - tamu_id (required|exists:tamu,id)
     *  - tanggal_kunjungan (nullable|date)
     *  - keperluan (nullable|string)
     *  - jenis_layanan (nullable|string) -> akan membuat record di tabel layanan
     *  - petugas_id (nullable|exists:users,id)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tamu_id' => ['required','exists:tamu,id'],
            'tanggal_kunjungan' => ['nullable','date'],
            'keperluan' => ['nullable','string','max:255'],
            'jenis_layanan' => ['nullable','string','max:150'],
            'petugas_id' => ['nullable','exists:users,id'],
        ]);

        $kunjungan = Kunjungan::create([
            'tamu_id' => $validated['tamu_id'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'] ?? Carbon::now('Asia/Jakarta'),
            'keperluan' => $validated['keperluan'] ?? null,
        ]);

        // Jika ada jenis layanan → buat record layanan
        if (!empty($validated['jenis_layanan']) || !empty($validated['petugas_id'])) {
            Layanan::create([
                'kunjungan_id' => $kunjungan->id,
                'petugas_id'   => $validated['petugas_id'] ?? null,
                'jenis_layanan'=> $validated['jenis_layanan'] ?? 'Lainnya',
                'deskripsi'    => $validated['keperluan'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Kunjungan berhasil dibuat.',
            'data' => $kunjungan->load(['tamu','layanan','petugas']),
        ], 201);
    }

    /**
     * GET /api/kunjungan/{id}
     */
    public function show(string $id)
    {
        $kunjungan = Kunjungan::with(['tamu','layanan.petugas','petugas'])->findOrFail($id);
        return response()->json($kunjungan);
    }

    /**
     * PUT /api/kunjungan/{id}
     */
    public function update(Request $request, string $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);

        $validated = $request->validate([
            'tamu_id' => ['sometimes','exists:tamu,id'],
            'tanggal_kunjungan' => ['sometimes','nullable','date'],
            'keperluan' => ['sometimes','nullable','string','max:255'],
            'jenis_layanan' => ['sometimes','nullable','string','max:150'],
            'petugas_id' => ['sometimes','nullable','exists:users,id'],
        ]);

        $kunjungan->fill($validated);
        $kunjungan->save();

        // Sinkronkan layanan bila dikirim
        if ($request->hasAny(['jenis_layanan','petugas_id'])) {
            $layanan = $kunjungan->layanan()->first();
            if (!$layanan) {
                $layanan = new Layanan(['kunjungan_id' => $kunjungan->id]);
            }
            if ($request->filled('jenis_layanan')) {
                $layanan->jenis_layanan = $validated['jenis_layanan'];
            }
            if ($request->exists('petugas_id')) {
                $layanan->petugas_id = $validated['petugas_id'] ?? null;
            }
            $layanan->save();
        }

        return response()->json([
            'message' => 'Kunjungan berhasil diperbarui.',
            'data' => $kunjungan->load(['tamu','layanan','petugas']),
        ]);
    }

    /**
     * DELETE /api/kunjungan/{id}
     */
    public function destroy(string $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);
        $kunjungan->delete();
        return response()->json(['message' => 'Data kunjungan berhasil dihapus']);
    }

    /**
     * GET /api/kunjungan/export/pdf
     * Params:
     *  - view, search (sama seperti index)
     */
    public function exportPdf(Request $request)
    {
        // Reuse query dari index (tanpa paginate)
        $requestAll = $request->all();
        $requestAll['per_page'] = 100000; // ambil semua
        $requestIndex = new Request($requestAll);
        $data = $this->index($requestIndex)->getData(true);

        $rows = $data['data'] ?? $data['data'] ?? [];

        $pdf = Pdf::loadView('ListKunjungan', [
            'rows' => $rows,
            'printed_at' => Carbon::now('Asia/Jakarta'),
            'view' => $request->query('view', 'today'),
            'search' => $request->query('search', ''),
        ])->setPaper('a4', 'portrait');

        $filename = 'daftar_kunjungan_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
