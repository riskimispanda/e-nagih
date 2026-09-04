<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ODC extends Model
{
    protected $table = 'odc';

    protected $fillable = [
        'nama_odc',
        'lokasi_id',
        'pon_port',
        'splitter_id',
        'port_out',
        'gps',
        'panjang_kabel',
        'rasio',
        'redaman',
        'modem_detail_id',
    ];

    public function modemDetail()
    {
        return $this->belongsTo(ModemDetail::class, 'modem_detail_id');
    }

    public function olt()
    {
        return $this->belongsTo(Lokasi::class,'lokasi_id');
    }

    public function parentSplitter()
    {
        return $this->belongsTo(ModemDetail::class, 'splitter_id');
    }

    public function odp()
    {
        return $this->hasMany(ODP::class, 'odc_id');
    }

    public function splitters()
    {
        return $this->hasMany(ModemDetail::class, 'odc_id');
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
