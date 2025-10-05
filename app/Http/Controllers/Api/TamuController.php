<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use App\Models\Tamu; // Menggunakan Model untuk best practice

class TamuController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        // PERBAIKAN: Menggunakan Model Tamu dan memperbaiki logika query pencarian.
        $query = Tamu::query()
            ->when($search, function ($q) use ($search) {
                // Mengelompokkan kondisi WHERE untuk pencarian yang akurat
                $q->where(function ($b) use ($search) {
                    $b->where('nama', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('asal_instansi', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%")
                      ->orWhere('jenis_kelamin', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama');

        $p = $query->paginate($perPage)->appends($request->query());

        // Tidak perlu mapping manual jika nama kolom sudah sesuai.
        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
            ],
        ]);
    }

    public function destroy($id)
    {
        // PENTING: Perlu diperhatikan integrity constraint.
        // Jika tamu memiliki data di tabel lain (kunjungan, layanan), delete ini akan gagal.
        Tamu::findOrFail($id)->delete();
        return response()->json(['message' => 'Tamu berhasil dihapus.']);
    }

    private function getExportData(Request $request)
    {
        $search = $request->query('search');

        return Tamu::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($b) use ($search) {
                    $b->where('nama', 'like', "%{$search}%")
                      ->orWhere('no_hp', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('asal_instansi', 'like', "%{$search}%")
                      ->orWhere('alamat', 'like', "%{$search}%")
                      ->orWhere('jenis_kelamin', 'like', "%{$search}%");
                });
            })
            ->orderBy('nama')
            ->get();
    }

    public function exportPdf(Request $request)
    {
        $rows = $this->getExportData($request);

        // PENTING: Pastikan Anda memiliki view di 'resources/views/pdf/ListTamuBlade.php'
        $pdf = Pdf::loadView('pdf.ListTamuBlade', [
            'rows' => $rows,
        ])->setPaper('a4', 'portrait');

        $name = 'daftar_tamu_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';
        return $pdf->download($name);
    }

    public function exportCsv(Request $request)
    {
        $rows = $this->getExportData($request)->map(function ($r) {
            return [
                'nama' => (string) $r->nama,
                'no_hp' => (string) $r->no_hp,
                'email' => (string) $r->email,
                'asal_instansi' => (string) $r->asal_instansi,
                'jenis_kelamin' => (string) $r->jenis_kelamin,
                'waktu_kunjungan' => $r->waktu_kunjungan ? Carbon::parse($r->waktu_kunjungan)->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '',
                'alamat' => (string) $r->alamat,
            ];
        });

        $filename = 'daftar_tamu_' . now('Asia/Jakarta')->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Nama', 'No. Whatsapp', 'Email', 'Asal Instansi', 'Jenis Kelamin', 'Waktu Kunjungan', 'Alamat']);

            foreach ($rows as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store',
            'Pragma'        => 'no-cache',
        ]);
    }
}