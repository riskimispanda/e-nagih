<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiketOpen extends Model
{
    protected $table = 'tiket_open';
    protected $fillable = [
        'customer_id',
        'kategori_id',
        'keterangan',
        'foto',
        'user_id',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function kategori()
    {
        return $this->belongsTo(KategoriTiket::class, 'kategori_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
