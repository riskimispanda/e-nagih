<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'paket';
    protected $fillable = [
        'nama_paket',
        'harga',
    ];

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'paket_id');
    }

}
