<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisKas extends Model
{
    protected $table = 'JenisKas';
    protected $fillable = [
        'jenis_kas'
    ];
    public function kas()
    {
        return $this->hasMany(Kas::class, 'kas_id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class,'kas_id');
    }

}
