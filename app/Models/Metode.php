<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metode extends Model
{
    protected $table = 'metode';
    protected $fillable = ['nama_metode', 'deskripsi'];
    
    // Relasi
    // public function transaksi()
    // {
    //     $this->hasMany(Invoice::class,'');
    // }

}
