<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanPiket extends Model
{
    protected $table = 'laporan_piket';
    protected $primaryKey = 'id_laporan';
    protected $fillable = [
        'id_piket','petugas_id','mulai_at','selesai_at','status_kehadiran','catatan'
    ];
    protected $casts = ['mulai_at'=>'datetime','selesai_at'=>'datetime'];

    public function piket(): BelongsTo
    {
        // jadwal berada di tabel 'piket' dengan PK 'id_piket'
        return $this->belongsTo(PiketPetugas::class, 'id_piket', 'id_piket');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
