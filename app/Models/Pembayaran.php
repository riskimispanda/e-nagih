<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = [
        'invoice_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_bayar',
        'keterangan',
        'bukti_bayar',
        'status_id',
        'user_id'
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
