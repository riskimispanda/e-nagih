<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModemDetail extends Model
{
    protected $table = 'ModemDetail';
    protected $fillable = [
        'logistik_id',
        'serial_number',
        'mac_address',
        'status_id',
        'customer_id'
    ];

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class, 'logistik_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

}
