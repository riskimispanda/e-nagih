<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriTiket extends Model
{
    protected $table = 'kategori_tiket';
    protected $fillable = [
        'nama_kategori',
    ];

    public function beritaAcara()
    {
        return $this->hasMany(BeritaAcara::class);
    }
}
