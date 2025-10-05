<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    protected $table = 'penilaian';
    protected $fillable = [
        'layanan_id','tamu_id','petugas_id','token','nilai','komentar','submitted_at'
    ];
    protected $casts = ['submitted_at' => 'datetime'];

    public function layanan(): BelongsTo { return $this->belongsTo(Layanan::class); }
    public function tamu(): BelongsTo     { return $this->belongsTo(Tamu::class); }
    public function petugas(): BelongsTo  { return $this->belongsTo(User::class, 'petugas_id'); }
}
