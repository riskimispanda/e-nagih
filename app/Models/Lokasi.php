<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    protected $table = 'lokasi';
    protected $fillable = [
        'nama_lokasi', 'jumlah_pon', 'id_server', 'gps', 'modem_detail_id'
    ];

    public function modemDetail()
    {
        return $this->belongsTo(ModemDetail::class, 'modem_detail_id');
    }

    /**
     * Daftar label Port PON (e.g. ['PON-01', 'PON-02', ...])
     */
    public function getPonListAttribute(): array
    {
        $count = (int) ($this->jumlah_pon ?: 8);
        $list = [];
        for ($i = 1; $i <= $count; $i++) {
            $list[] = 'PON-' . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
        return $list;
    }
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
