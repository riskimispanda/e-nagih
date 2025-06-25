<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ODP extends Model
{
    protected $table = 'odp';
    protected $fillable = ['nama_odp', 'odc_id'];

    public function odc()
    {
        return $this->belongsTo(ODC::class);
    }
    
    public function customer()
    {
        return $this->hasMany(Customer::class, 'lokasi_id');
    }
}
