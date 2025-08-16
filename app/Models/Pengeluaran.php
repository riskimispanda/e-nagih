<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = [
        'jumlah_pengeluaran',
        'jenis_pengeluaran',
        'keterangan',
        'tanggal_pengeluaran',
        'bukti_pengeluaran',
        'user_id',
        'status_id',
        'metode_bayar',
        'alasan',
        'rab_id',
        'kas_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class, 'rab_id');
    }

    public function kas()
    {
        return $this->belongsTo(JenisKas::class, 'kas_id');
    }

}
