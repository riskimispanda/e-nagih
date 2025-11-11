<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    const TIPE_REGULER = 'reguler';
    const TIPE_DISKON = 'diskon';

    protected $table = 'pembayaran';
    protected $fillable = [
        'invoice_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_bayar',
        'keterangan',
        'bukti_bayar',
        'status_id',
        'user_id',
        'saldo',
        'admin_id',
        'jumlah_bayar_baru',
        'ket_edit',
        'tipe_pembayaran',
        'metode_bayar_new'
    ];

    protected $attributes = [
        'tipe_pembayaran' => self::TIPE_REGULER, // nilai default
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Method untuk mendapatkan opsi tipe pembayaran
    public static function getTipePembayaranOptions()
    {
        return [
            self::TIPE_REGULER => 'Pembayaran Reguler',
            self::TIPE_DISKON => 'Pembayaran Diskon',
        ];
    }

    // Method untuk mendapatkan label tipe pembayaran
    public function getTipePembayaranLabelAttribute()
    {
        return self::getTipePembayaranOptions()[$this->tipe_pembayaran] ?? 'Tidak Diketahui';
    }

    // Scope untuk query yang sering digunakan
    public function scopeReguler($query)
    {
        return $query->where('tipe_pembayaran', self::TIPE_REGULER);
    }

    public function scopeDiskon($query)
    {
        return $query->where('tipe_pembayaran', self::TIPE_DISKON);
    }
}
