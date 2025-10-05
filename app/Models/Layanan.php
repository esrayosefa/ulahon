<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = [
        'kunjungan_id',
        'petugas_id',
        'jenis_layanan',
        'deskripsi',
        'kebutuhan_data',
        'foto_bukti_path',
    ];

    protected $appends = ['foto_bukti_url'];

    // === RELASI ===
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    // === ACCESSOR ===
    public function getFotoBuktiUrlAttribute(): ?string
    {
        return $this->foto_bukti_path ? Storage::url($this->foto_bukti_path) : null;
    }
}
