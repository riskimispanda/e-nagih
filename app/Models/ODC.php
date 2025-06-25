<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ODC extends Model
{
    protected $table = 'odc';

    protected $fillable = [
        'nama_odc',
        'lokasi_id',
    ];

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }

    public function odp()
    {
        return $this->hasMany(ODP::class, 'odc_id');
    }

    public function customer()
    {
        return $this->hasMany(Customer::class, 'lokasi_id');
    }
}
