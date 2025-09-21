<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $table = 'BeritaAcara';
    protected $fillable = [
        'customer_id',
        'invoice_id',
        'tanggal_ba',
        'tanggal_selesai_ba',
        'keterangan',
        'kategori_tiket',
        'admin_id',
        'noc_id'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class,'invoice_id');
    }

    public function tiket(){
        return $this->belongsTo(KategoriTiket::class,'kategori_tiket');
    }

    public function admin(){
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function noc()
    {
        return $this->belongsTo(User::class, 'noc_id');
    }

}
