<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $table = 'server';
    protected $fillable = [
        'lokasi_server',
        'ip_address',
        'gps'
    ];

    public function lokasi()
    {
        return $this->hasMany(Lokasi::class, 'id_server');
    }
}
