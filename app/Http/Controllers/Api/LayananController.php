<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LayananController extends Controller
{
    /**
     * GET /api/layanan?search=&per_page=12
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 12);
        $search  = trim((string) $request->query('search', ''));

        $q = Layanan::query()
            ->with([
                'petugas:id,name,foto',
                'kunjungan.tamu:id,nama,asal_instansi',
            ]);

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('jenis_layanan', 'like', "%{$search}%")
                   ->orWhere('deskripsi', 'like', "%{$search}%")
                   ->orWhere('kebutuhan_data', 'like', "%{$search}%")
                   ->orWhereHas('kunjungan.tamu', function ($t) use ($search) {
                       $t->where('nama', 'like', "%{$search}%")
                         ->orWhere('asal_instansi', 'like', "%{$search}%");
                   });
            });
        }

        $paginator = $q->orderByDesc('created_at')->paginate($perPage);
        return response()->json($paginator);
    }

    /**
     * POST /api/layanan  (multipart/form-data)
     * Field: kunjungan_id*, petugas_id*, jenis_layanan*, deskripsi, kebutuhan_data, foto_bukti (image)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'kunjungan_id'   => ['required','exists:kunjungan,id'],
            'petugas_id'     => ['required','exists:users,id'],
            'jenis_layanan'  => ['required','string','max:150'],
            'deskripsi'      => ['nullable','string'],
            'kebutuhan_data' => ['nullable','string'],
            'foto_bukti'     => ['nullable','image','mimes:jpg,jpeg,png,webp','max:3072'],
        ]);

        if ($request->hasFile('foto_bukti')) {
            $data['foto_bukti_path'] = $request->file('foto_bukti')->store('layanan', 'public');
        }

        $layanan = Layanan::create($data)->load(['petugas','kunjungan.tamu']);

        return response()->json([
            'message' => 'Laporan layanan dibuat',
            'data' => $layanan,
        ], 201);
    }

    /**
     * GET /api/layanan/{id}
     */
    public function show(string $id)
    {
        return Layanan::with(['petugas','kunjungan.tamu'])->findOrFail($id);
    }

    /**
     * PUT /api/layanan/{id}  (multipart/form-data)
     */
    public function update(Request $request, string $id)
    {
        $layanan = Layanan::findOrFail($id);

        $data = $request->validate([
            'petugas_id'     => ['sometimes','required','exists:users,id'],
            'jenis_layanan'  => ['sometimes','required','string','max:150'],
            'deskripsi'      => ['sometimes','nullable','string'],
            'kebutuhan_data' => ['sometimes','nullable','string'],
            'foto_bukti'     => ['sometimes','nullable','image','mimes:jpg,jpeg,png,webp','max:3072'],
        ]);

        if ($request->hasFile('foto_bukti')) {
            if ($layanan->foto_bukti_path) {
                Storage::disk('public')->delete($layanan->foto_bukti_path);
            }
            $data['foto_bukti_path'] = $request->file('foto_bukti')->store('layanan', 'public');
        }

        $layanan->fill($data)->save();

        return response()->json([
            'message' => 'Laporan layanan diperbarui',
            'data' => $layanan->fresh()->load(['petugas','kunjungan.tamu']),
        ]);
    }

    /**
     * DELETE /api/layanan/{id}
     */
    public function destroy(string $id)
    {
        $layanan = Layanan::findOrFail($id);

        if ($layanan->foto_bukti_path) {
            Storage::disk('public')->delete($layanan->foto_bukti_path);
        }
        $layanan->delete();

        return response()->json(['message' => 'Laporan layanan dihapus']);
    }
}
