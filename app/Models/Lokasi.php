<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $fillable = [
        'nama_lokasi','id_server','gps'
    ];
    public function customer()
    {
        return $this->hasMany(Customer::class, 'lokasi_id');
    }
    public function odc()
    {
        return $this->hasMany(ODC::class);
    }
    public function odp()
    {
        return $this->hasMany(ODP::class, 'lokasi_id');
    }
    public function olt()
    {
        return $this->hasMany(ODC::class);
    }
    public function server()
    {
        return $this->belongsTo(Server::class, 'id_server');
    }
}
