<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerusahaanCorporate extends Model
{
  protected $table = 'pembayaran_corporate';
  protected $fillable = [
    'invoice_corporate_id',
    'perusahaan_id',
    'user_id',
    'tanggal_bayar',
    'jumlah_bayar',
    'metode_bayar',
    'bukti_bayar',
    'keterangan',
    'status_id'
  ];

  public function perusahaan()
  {
    return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
  }

  public function user()
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function status()
  {
    return $this->belongsTo(Status::class, 'status_id');
  }
}
