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
    ];

    public function customer()
    {
        return $this->hasMany(Customer::class, 'perangkat_id');
    }

}
