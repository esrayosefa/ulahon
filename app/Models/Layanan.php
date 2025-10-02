<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = [
        'tamu_id',
        'keperluan',
        'rincian_layanan',
        // ✅ Tambahkan kunjungan_id untuk relasi ke antrian
        'kunjungan_id', 
        'created_at',
    ];

    public $timestamps = false; //

    // Relasi ke model Tamu
    public function tamu(): BelongsTo
    {
        return $this->belongsTo(Tamu::class, 'tamu_id');
    }

    // Relasi ke model Kunjungan (Antrian yang telah dilayani)
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }
}