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
        'rasio',
        'pon_port',
        'lokasi_id',
        'odc_id',
        'odp_id',
        'parent_splitter_id',
        'parent_port_out',
        'status_id',
        'customer_id',
        'cek',
        'tanggal_terpakai'
    ];

    protected $casts = [
        'tanggal_terpakai' => 'datetime',
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

    public function odc()
    {
        return $this->belongsTo(ODC::class, 'odc_id');
    }

    public function olt()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }

    public function oltOdcs()
    {
        return $this->hasMany(ODC::class, 'splitter_id');
    }

    public function odp()
    {
        return $this->belongsTo(ODP::class, 'odp_id');
    }

    public function parentSplitter()
    {
        return $this->belongsTo(ModemDetail::class, 'parent_splitter_id');
    }

    public function childSplitters()
    {
        return $this->hasMany(ModemDetail::class, 'parent_splitter_id');
    }

    public function odps()
    {
        return $this->hasMany(ODP::class, 'splitter_id');
    }

    /**
     * Kapasitas port keluaran berdasarkan rasio (1:4 => 4, 1:8 => 8, 1:16 => 16).
     */
    public function getKapasitasAttribute(): int
    {
        $rasio = explode(':', $this->rasio ?? '1:0');
        return (int) end($rasio);
    }

    /**
     * Label lokasi terpasang (ODC atau ODP).
     */
    public function getLokasiTerpasangAttribute(): ?string
    {
        if ($this->odc_id) {
            return 'ODC: ' . ($this->odc->nama_odc ?? '-');
        }
        if ($this->odp_id) {
            return 'ODP: ' . ($this->odp->nama_odp ?? '-');
        }
        return null;
    }

    public function scopeTerpakai($query)
    {
        return $query->where('status_id', 13);
    }

    public function scopeTersedia($query)
    {
        return $query->where('status_id', 14);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status_id', 4);
    }

    public function scopeRusak($query)
    {
        return $query->where('status_id', 15);
    }

}
