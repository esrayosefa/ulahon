<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TukarPiket extends Model
{
    protected $table = 'tukar_piket';
    protected $primaryKey = 'id_tukar';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_piket_awal',
        'id_piket_tukar',
        'tanggal_awal',
        'tanggal_tukar',
        'sesi_awal',
        'sesi_tukar',
        'petugas_tukar',
        'alasan',
        'status', // pending|disetujui|ditolak
    ];

    /* ===== RELASI =====
       Asumsikan model jadwal kamu bernama PiketPetugas dan:
       - $table = 'piket'
       - $primaryKey = 'id_piket'
       - ada relasi petugas() -> belongsTo(User::class,'petugas_id')
    */
    public function awal(): BelongsTo
    {
        return $this->belongsTo(PiketPetugas::class, 'id_piket_awal', 'id_piket');
    }

    public function tukar(): BelongsTo
    {
        return $this->belongsTo(PiketPetugas::class, 'id_piket_tukar', 'id_piket');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_tukar');
    }
}
