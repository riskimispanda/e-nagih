<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    protected $table = 'perusahaan';
    protected $fillable = [
        'nama_perusahaan',
        'alamat',
        'no_hp',
        'gps',
        'user_id',
        'admin_id',
        'paket_id',
        'status_id',
        'tanggal',
        'speed',
        'upload',
        'download',
        'foto',
        'harga',
        'nama_pic',
        'ip_address',
        'media',
        'seri_perangkat',
        'mac_address',
        'perangkat_id',
        'server'
    ];

    public function usr()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_id');
    }

}
