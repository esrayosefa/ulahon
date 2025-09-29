<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TamuController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->query('search');
        $perPage = (int) $request->query('per_page', 10);

        $q = DB::table('tamu')
            ->when($search, function ($b) use ($search) {
                $b->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('jenis_kelamin', 'like', "%{$search}%");
            })
            ->orderBy('nama');

        $p = $q->paginate($perPage)->appends($request->query());

        return response()->json([
            'data' => collect($p->items())->map(function ($r) {
                return [
                    'id'              => $r->id,
                    'nama'            => $r->nama,
                    'no_hp'           => $r->no_hp,
                    'email'           => $r->email,
                    'asal_instansi'   => $r->asal_instansi,
                    'jenis_kelamin'   => $r->jenis_kelamin,
                    'waktu_kunjungan' => $r->waktu_kunjungan,
                    'alamat'          => $r->alamat,
                ];
            }),
            'meta' => [
                'total'        => $p->total(),
                'per_page'     => $p->perPage(),
                'current_page' => $p->currentPage(),
            ],
        ]);
    }

    public function destroy($id)
    {
        DB::table('tamu')->where('id', $id)->delete();
        return response()->json(['message' => 'Tamu dihapus.']);
    }

    public function exportPdf(Request $request)
    {
        $search = $request->query('search');

        $rows = DB::table('tamu')
            ->when($search, function ($b) use ($search) {
                $b->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('jenis_kelamin', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('pdf.ListTamuBlade', [
            'rows' => $rows,
        ])->setPaper('a4', 'portrait');

        $name = 'daftar_tamu_' . now('Asia/Jakarta')->format('Ymd_His') . '.pdf';
        return $pdf->download($name);
    }

    public function exportCsv(Request $request)
    {
        $search = $request->query('search');

        $rows = DB::table('tamu')
            ->when($search, function ($b) use ($search) {
                $b->where('nama', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('asal_instansi', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('jenis_kelamin', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->get(['nama','no_hp','email','asal_instansi','jenis_kelamin','waktu_kunjungan','alamat']);

        $filename = 'daftar_tamu_' . now('Asia/Jakarta')->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            // Header
            fputcsv($out, ['Nama','No. Whatsapp','Email','Asal Instansi','Jenis Kelamin','Waktu Kunjungan','Alamat']);

            // Rows
            foreach ($rows as $r) {
                fputcsv($out, [
                    (string) $r->nama,
                    (string) $r->no_hp, // jaga leading zero
                    (string) $r->email,
                    (string) $r->asal_instansi,
                    (string) $r->jenis_kelamin,
                    $r->waktu_kunjungan ? \Carbon\Carbon::parse($r->waktu_kunjungan)->timezone('Asia/Jakarta')->format('Y-m-d H:i') : '',
                    (string) $r->alamat,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'  => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'max-age=0, must-revalidate, no-cache, no-store',
            'Pragma'        => 'no-cache',
        ]);
    }
}