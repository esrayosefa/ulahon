<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiketPetugas extends Model
{
    protected $table = 'piket';
    protected $primaryKey = 'id_piket';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['tanggal','sesi','petugas_id','catatan'];

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}

