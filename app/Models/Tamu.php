<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Tamu extends Model
{
    use HasFactory;

    protected $table = 'tamu';

    protected $fillable = [
        'nama',
        'no_hp',
        'asal_instansi', // pastikan sama dengan kolom di DB/seeder
    ];

    /** Tamu punya banyak Kunjungan */
    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'tamu_id', 'id');
    }

    /**
     * Tamu ->(hasManyThrough)-> Layanan lewat Kunjungan
     * through: Kunjungan (FK: tamu_id → Tamu.id)
     * target : Layanan  (FK: kunjungan_id → Kunjungan.id)
     */
    public function layanan(): HasManyThrough
    {
        return $this->hasManyThrough(
            Layanan::class,     // target
            Kunjungan::class,   // through
            'tamu_id',          // FK di tabel kunjungan menunjuk ke Tamu
            'kunjungan_id',     // FK di tabel layanan menunjuk ke Kunjungan
            'id',               // PK di Tamu
            'id'                // PK di Kunjungan
        );
    }
}
