<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BeritaAcara extends Model
{
    protected $table = 'BeritaAcara';

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'tanggal_ba',
        'tanggal_selesai_ba',
        'keterangan',
        'kategori_tiket',
        'admin_id',
        'noc_id'
    ];

    protected $dates = [
        'tanggal_ba',
        'tanggal_selesai_ba',
    ];

    protected $casts = [
        'tanggal_ba' => 'date',
        'tanggal_selesai_ba' => 'date',
    ];

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relationship with Invoice
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Relationship with KategoriTiket
     */
    public function tiket()
    {
        return $this->belongsTo(KategoriTiket::class, 'kategori_tiket');
    }

    /**
     * Relationship with Admin User
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relationship with NOC User
     */
    public function noc()
    {
        return $this->belongsTo(User::class, 'noc_id');
    }

    /**
     * Scope for expired BeritaAcara - only based on tanggal_selesai_ba
     */
    public function scopeExpired($query)
    {
        return $query->where('tanggal_selesai_ba', '<', now()->toDateString());
    }

    /**
     * Scope for not expired - only based on tanggal_selesai_ba
     */
    public function scopeNotExpired($query)
    {
        return $query->where('tanggal_selesai_ba', '>=', now()->toDateString());
    }

    /**
     * Scope for currently active BeritaAcara - only based on tanggal_selesai_ba
     */
    public function scopeCurrentlyActive($query)
    {
        return $query->where('tanggal_selesai_ba', '>=', now()->toDateString());
    }

    /**
     * Check if BeritaAcara is expired - only based on tanggal_selesai_ba
     */
    public function isExpired(): bool
    {
        return $this->tanggal_selesai_ba < now()->toDateString();
    }

    /**
     * Check if BeritaAcara is active - only based on tanggal_selesai_ba
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get days remaining until expiry
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->tanggal_selesai_ba, false);
    }

    /**
     * Get formatted tanggal_ba
     */
    public function getFormattedTanggalBaAttribute(): string
    {
        return $this->tanggal_ba ? $this->tanggal_ba->format('d-m-Y') : '-';
    }

    /**
     * Get formatted tanggal_selesai_ba
     */
    public function getFormattedTanggalSelesaiBaAttribute(): string
    {
        return $this->tanggal_selesai_ba ? $this->tanggal_selesai_ba->format('d-m-Y') : '-';
    }

    /**
     * Extend expiry date
     */
    public function extendExpiry(int $days): bool
    {
        $this->tanggal_selesai_ba = Carbon::parse($this->tanggal_selesai_ba)->addDays($days);

        return $this->save();
    }

    /**
     * Get status badge color for UI - simplified based on tanggal_selesai_ba only
     */
    public function getStatusBadgeColorAttribute(): string
    {
        if ($this->isExpired()) {
            return 'danger';
        }

        // Check if close to expiry (within 3 days)
        if ($this->days_remaining <= 3) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * Get status text for display - simplified based on tanggal_selesai_ba only
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        if ($this->days_remaining <= 3) {
            return 'Akan Expired';
        }

        return 'Aktif';
    }

    /**
     * Get human readable time remaining
     */
    public function getTimeRemainingAttribute(): string
    {
        if ($this->isExpired()) {
            $daysOverdue = now()->diffInDays($this->tanggal_selesai_ba);
            return "Expired {$daysOverdue} hari yang lalu";
        }

        $daysRemaining = $this->days_remaining;

        if ($daysRemaining == 0) {
            return 'Expired hari ini';
        } elseif ($daysRemaining == 1) {
            return 'Expired besok';
        } else {
            return "Sisa {$daysRemaining} hari";
        }
    }

    /**
     * Check if BeritaAcara is expiring soon (within specified days)
     */
    public function isExpiringSoon(int $days = 3): bool
    {
        if ($this->isExpired()) {
            return false;
        }

        return $this->days_remaining <= $days;
    }

    /**
     * Get all active BeritaAcara that are expiring soon
     */
    public static function getExpiringSoon(int $days = 3)
    {
        $targetDate = now()->addDays($days)->toDateString();

        return static::where('tanggal_selesai_ba', '<=', $targetDate)
            ->where('tanggal_selesai_ba', '>=', now()->toDateString())
            ->with(['customer', 'invoice'])
            ->get();
    }

    /**
     * Get all expired BeritaAcara
     */
    public static function getExpired()
    {
        return static::where('tanggal_selesai_ba', '<', now()->toDateString())
            ->with(['customer', 'invoice'])
            ->get();
    }

    /**
     * Get all currently active BeritaAcara
     */
    public static function getCurrentlyActive()
    {
        return static::where('tanggal_selesai_ba', '>=', now()->toDateString())
            ->with(['customer', 'invoice'])
            ->get();
    }

    /**
     * Check if customer has active BeritaAcara
     */
    public static function hasActiveForCustomer(int $customerId): bool
    {
        return static::where('customer_id', $customerId)
            ->where('tanggal_selesai_ba', '>=', now()->toDateString())
            ->exists();
    }

    /**
     * Get active BeritaAcara for specific customer
     */
    public static function getActiveForCustomer(int $customerId)
    {
        return static::where('customer_id', $customerId)
            ->where('tanggal_selesai_ba', '>=', now()->toDateString())
            ->orderBy('tanggal_selesai_ba', 'desc')
            ->first();
    }
}