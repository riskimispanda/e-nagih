<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
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
        'cek',
        'warning_sent',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'tanggal_selesai' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Trigger ketika customer di-soft delete
        static::deleting(function ($customer) {
            if ($customer->isForceDeleting()) {
                // Hard delete - skip logic kita
                Log::info("Hard delete customer {$customer->nama_customer} - skip return perangkat");
                return;
            }

            // Soft delete - kembalikan perangkat ke stok
            Log::info("Soft delete customer {$customer->nama_customer} - kembalikan perangkat ke stok");
            $customer->kembalikanPerangkatKeStok();
        });

        // Optional: Trigger ketika customer di-restore
        static::restored(function ($customer) {
            Log::info("Customer {$customer->nama_customer} di-restore - kurangi stok perangkat");
            $customer->kurangiStokPerangkat();
        });
    }

    /**
     * Kembalikan perangkat ke stok ketika customer dideaktivasi
     */
    public function kembalikanPerangkatKeStok()
    {
        try {
            if ($this->perangkat_id) {
                $perangkat = Perangkat::find($this->perangkat_id);

                if ($perangkat) {
                    // Tambah stok perangkat
                    $perangkat->increment('jumlah_stok');

                    // Log activity
                    activity()
                        ->performedOn($this)
                        ->log("Perangkat {$perangkat->nama_perangkat} dikembalikan ke stok - Customer {$this->nama_customer} dideaktivasi");

                    Log::info("✅ Perangkat {$perangkat->nama_perangkat} (ID: {$perangkat->id}) dikembalikan ke stok. Stok sekarang: {$perangkat->jumlah_stok}");
                } else {
                    Log::warning("⚠️ Perangkat dengan ID {$this->perangkat_id} tidak ditemukan untuk customer {$this->nama_customer}");
                }
            } else {
                Log::info("ℹ️ Customer {$this->nama_customer} tidak memiliki perangkat (perangkat_id = null)");
            }
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengembalikan perangkat ke stok untuk customer {$this->nama_customer}: " . $e->getMessage());
        }
    }

    /**
     * Kurangi stok perangkat ketika customer diaktifkan kembali
     */
    public function kurangiStokPerangkat()
    {
        try {
            if ($this->perangkat_id) {
                $perangkat = Perangkat::find($this->perangkat_id);

                if ($perangkat && $perangkat->jumlah_stok > 0) {
                    // Kurangi stok perangkat
                    $perangkat->decrement('jumlah_stok');

                    Log::info("📦 Stok perangkat {$perangkat->nama_perangkat} dikurangi. Stok sekarang: {$perangkat->jumlah_stok}");
                } else {
                    Log::warning("⚠️ Tidak bisa kurangi stok - perangkat tidak ditemukan atau stok habis");
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengurangi stok perangkat: " . $e->getMessage());
        }
    }

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
