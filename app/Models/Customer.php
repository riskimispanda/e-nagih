<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $fillable = [
        'nama_customer',
        'email',
        'no_hp',
        'alamat',
        'identitas',
        'no_identitas',
        'gps',
        'paket_id',
        'lokasi_id',
        'teknisi_id',
        'agen_id',
        'status_id',
        'router_id',
        'koneksi_id',
        'perangkat_id',
        'mac_address',
        'seri_perangkat',
        'usersecret',
        'pass_secret',
        'remote_address',
        'local_address',
        'foto_rumah',
        'foto_perangkat',
        'panjang_kabel',
        'redaman',
        'transiver',
        'resiver',
        'media_id',
        'tanggal_selesai',
        'access_point',
        'station',
        'remote',
        'cek'
    ];


    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function odp()
    {
        return $this->belongsTo(ODP::class, 'lokasi_id');
    }
    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
    public function agen()
    {
        return $this->belongsTo(User::class, 'agen_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function router()
    {
        return $this->belongsTo(Router::class, 'router_id');
    }
    public function koneksi()
    {
        return $this->belongsTo(Koneksi::class, 'koneksi_id');
    }
    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_id');
    }
    
    public function media()
    {
        return $this->belongsTo(MediaKoneksi::class, 'media_id');
    }

    public function getServer()
    {
        return $this->hasOne(Server::class,'id','lokasi_id');
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class,'customer_id');
    }

    public function beritaAcara()
    {
        return $this->hasMany(BeritaAcara::class, 'customer_id');
    }

    public function tiket()
    {
        return $this->hasMany(TiketOpen::class,'customer_id');
    }

    public function activeBeritaAcara()
    {
        return $this->hasOne(BeritaAcara::class, 'customer_id')
            ->where('tanggal_selesai_ba', '>=', now()->toDateString())
            ->orderBy('tanggal_selesai_ba', 'desc');
    }

    /**
     * Check if customer has active BeritaAcara
     */
    public function hasActiveBeritaAcara(): bool
    {
        return $this->activeBeritaAcara()->exists();
    }

    /**
     * Get the latest active BeritaAcara
     */
    public function getActiveBeritaAcaraAttribute()
    {
        return $this->activeBeritaAcara()->first();
    }

    public function isBlocked(): bool
    {
        return $this->status_id == 9;
    }

    /**
     * Check if customer is active
     */
    public function isActive(): bool
    {
        return $this->status_id == 1; // Adjust status ID as needed
    }

    /**
     * Get customer's unpaid invoices
     */
    public function unpaidInvoices()
    {
        return $this->invoice()->where('status_id', 7); // Adjust status ID as needed
    }

    /**
     * Get customer's latest invoice
     */
    public function latestInvoice()
    {
        return $this->hasOne(Invoice::class, 'customer_id')->latest('created_at');
    }
}
