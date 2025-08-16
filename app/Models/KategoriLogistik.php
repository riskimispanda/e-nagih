<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriLogistik extends Model
{
    protected $table = 'KategoriLogistik';
    protected $fillable = [
        'nama_logistik'
    ];

    public function logistik()
    {
        return $this->hasMany(Perangkat::class);
    }

}
