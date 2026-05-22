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
    'perangkat',
    'router_id',
    'server',
    'paket',
    'foto_perangkat',
    'foto_tempat'
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



  /**
   * Relasi ke Router
   */
  public function router()
  {
    return $this->belongsTo(Router::class, 'router_id');
  }

  /**
   * Relasi ke Invoice Corporate
   */
  public function invoices()
  {
    return $this->hasMany(InvoiceCorporate::class, 'perusahaan_id');
  }

  /**
   * Get latest invoice
   */
  public function latestInvoice()
  {
    return $this->hasOne(InvoiceCorporate::class, 'perusahaan_id')->latest('created_at');
  }

  /**
   * Get unpaid invoices
   */
  public function unpaidInvoices()
  {
    return $this->invoices()->where('status_id', 7); // Sesuaikan dengan status unpaid
  }

}
