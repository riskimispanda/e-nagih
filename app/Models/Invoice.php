<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    protected $table = 'invoice';
    protected $fillable = [
        'customer_id',
        'status_id',
        'paket_id',
        'tagihan',
        'jatuh_tempo',
        'reference',
        'merchant_ref',
        'metode_bayar',
        'tambahan',
        'tanggal_blokir',
        'saldo',
        'tunggakan',
        'cek'
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}
