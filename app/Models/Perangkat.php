<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perangkat extends Model
{
    protected $table = 'perangkat';
    protected $fillable = [
        'nama_perangkat',
        'jumlah_stok',
        'harga',
        'kategori_id'
    ];

    public function customer()
    {
        return $this->hasMany(Customer::class, 'perangkat_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriLogistik::class);
    }

}
