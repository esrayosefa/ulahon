<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';

    protected $fillable = ['tamu_id','tanggal_kunjungan','keperluan'];
    protected $casts = ['tanggal_kunjungan' => 'datetime'];

    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class, 'tamu_id');
    }

    public function layanan(): HasOne
    {
        return $this->hasOne(Layanan::class, 'kunjungan_id');
    }

    public function petugas(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Layanan::class,
            'kunjungan_id', // FK di layanan → kunjungan
            'id',           // PK di users
            'id',           // PK di kunjungan
            'petugas_id'    // FK di layanan → users
        );
    }
}
