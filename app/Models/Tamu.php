<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tamu extends Model
{
    protected $table = 'tamu';
    
    // Sesuaikan field fillable dengan kolom di tabel `tamu` Anda
    protected $fillable = ['nama', 'instansi', 'alamat', 'telepon', 'email'];
    
    public $timestamps = true; 

    // Relasi: Satu Tamu bisa memiliki banyak Kunjungan
    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'tamu_id');
    }
}