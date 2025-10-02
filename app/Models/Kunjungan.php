<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    
    // Sesuaikan field fillable dengan kolom di tabel `kunjungan` Anda
    protected $fillable = ['tamu_id', 'keperluan']; 
    
    public $timestamps = true; 

    // Relasi: Setiap Kunjungan dimiliki oleh satu Tamu
    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class, 'tamu_id');
    }

    // Relasi: Kunjungan ini mungkin sudah memiliki Layanan yang terkait (artinya sudah dilayani)
    public function layanan(): HasOne
    {
        return $this->hasOne(Layanan::class, 'kunjungan_id');
    }
}