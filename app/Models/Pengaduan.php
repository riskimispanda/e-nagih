<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    protected $table = 'pengaduan';
    protected $fillable = [
        'customer_id',
        'pengaduan_id',
        'status_id',
        'teknisi_id',
        'judul',
        'deskripsi',
        'lampiran',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function pengaduan()
    {
        return $this->belongsTo(JPengaduan::class, 'pengaduan_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}
