<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $table = 'router';
    protected $fillable = ['nama_router', 'username', 'password', 'ip_address', 'port'];

    public function paket()
    {
        return $this->hasMany(Paket::class, 'router_id');
    }
}
