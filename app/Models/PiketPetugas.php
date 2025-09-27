<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiketPetugas extends Model
{
    protected $table = 'piket_petugas';

    protected $fillable = ['user_id', 'tanggal', 'sesi'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
