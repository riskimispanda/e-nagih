<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceCorporate extends Model
{

  protected $table = 'invoice_corporate';

  protected $fillable = [
    'perusahaan_id',
    'status_id',
    'invoice_number',
    'tagihan',
    'tambahan',
    'keterangan_tambahan',
    'tanggal_invoice',
    'jatuh_tempo',
    'reference',
    'merchant_ref',
    'metode_bayar',
    'saldo',
    'tunggakan',
    'cek',
  ];

  protected $casts = [
    'tanggal_invoice' => 'date',
    'jatuh_tempo' => 'date',
    'tagihan' => 'decimal:2',
    'tambahan' => 'decimal:2',
    'saldo' => 'decimal:2',
    'tunggakan' => 'decimal:2',
    'cek' => 'boolean',
  ];

  /**
   * Boot method untuk auto-generate invoice number
   */
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($invoice) {
      if (empty($invoice->invoice_number)) {
        $invoice->invoice_number = static::generateInvoiceNumber();
      }
    });
  }

  /**
   * Generate unique invoice number
   * Format: INV-CORP-YYYYMM-XXXX
   */
  public static function generateInvoiceNumber()
  {
    $prefix = 'INV-CORP-';
    $yearMonth = date('Ym');

    // Get last invoice number for this month
    $lastInvoice = static::whereRaw("invoice_number LIKE ?", [$prefix . $yearMonth . '%'])
      ->orderBy('invoice_number', 'desc')
      ->first();

    if ($lastInvoice) {
      $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
      $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    } else {
      $newNumber = '0001';
    }

    return $prefix . $yearMonth . '-' . $newNumber;
  }

  /**
   * Calculate Total Tagihan
   */
  public function calculateTotal()
  {
    $this->total_tagihan = $this->tagihan + $this->tambahan;
  }

  /**
   * Relasi ke Pembayaran
   */
  public function pembayaran()
  {
    return $this->hasMany(Pembayaran::class, 'invoice_id');
  }

  /**
   * Relasi ke Perusahaan
   */
  public function perusahaan()
  {
    return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
  }

  /**
   * Relasi ke Status
   */
  public function status()
  {
    return $this->belongsTo(Status::class, 'status_id');
  }



  /**
   * Relasi ke User (Created By)
   */
  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  /**
   * Relasi ke User (Updated By)
   */
  public function updater()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  /**
   * Check if invoice is overdue
   */
  public function isOverdue(): bool
  {
    return $this->jatuh_tempo < now() && $this->status_id != 1; // Sesuaikan status_id paid
  }

  /**
   * Get formatted tagihan
   */
  public function getFormattedTagihanAttribute()
  {
    return 'Rp ' . number_format($this->tagihan, 0, ',', '.');
  }


}
