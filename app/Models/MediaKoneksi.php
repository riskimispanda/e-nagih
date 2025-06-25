<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MediaKoneksi extends Model
{
    protected $table = 'media_koneksi';
    protected $fillable = [
        'nama_media',
        'customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
