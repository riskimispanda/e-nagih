<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    protected $table = 'pendapatan';
    protected $fillable = [
        'jumlah_pendapatan',
        'jenis_pendapatan',
        'deskripsi',
        'tanggal',
        'bukti_pendapatan',
        'metode_bayar',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
