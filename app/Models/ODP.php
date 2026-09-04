<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ODP extends Model
{
    protected $table = 'odp';
    protected $fillable = ['nama_odp', 'odc_id', 'splitter_id', 'gps', 'panjang_kabel', 'rasio', 'port_out', 'redaman', 'modem_detail_id'];

    public function modemDetail()
    {
        return $this->belongsTo(ModemDetail::class, 'modem_detail_id');
    }

    public function odc()
    {
        return $this->belongsTo(ODC::class, 'odc_id');
    }

    public function splitter()
    {
        return $this->belongsTo(ModemDetail::class, 'splitter_id');
    }

    public function splitters()
    {
        return $this->hasMany(ModemDetail::class, 'odp_id');
    }
    
    public function customer()
    {
        return $this->hasMany(Customer::class, 'lokasi_id');
    }

    /**
     * Jumlah port sesuai rasio rencana (1:8 => 8, 1:16 => 16).
     */
    public function getKapasitasRencanaAttribute(): int
    {
        $rasio = explode(':', $this->rasio ?? '1:0');
        return (int) end($rasio);
    }
}
