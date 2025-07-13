<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'status';
    protected $fillable = [
        'nama_status',
    ];

    public function customer()
    {
        return $this->hasMany(Customer::class, 'status_id');
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class, 'status_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'status_id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'status_id');
    }

    public function kas()
    {
        return $this->hasMany(Kas::class, 'status_id');
    }

}
