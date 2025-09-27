<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = [
        'tamu_id',
        'keperluan',
        'rincian_layanan',
        'created_at',
    ];

    public $timestamps = false; // karena kamu tidak punya `updated_at`
}
