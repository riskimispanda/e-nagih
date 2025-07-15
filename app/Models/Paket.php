<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $table = 'paket';
    protected $fillable = [
        'nama_paket',
        'harga',
        'router_id'
    ];

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'paket_id');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'paket_id');
    }

    public function router()
    {
        return $this->belongsTo(Router::class, 'router_id');
    }
}
