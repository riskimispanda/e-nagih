<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dismantle extends Model
{
    protected $table = 'dismantles';

    protected $fillable = [
        'modem_detail_id',
        'perangkat_id',
        'serial_number',
        'mac_address',
        'customer_id_lama',
        'customer_nama',
        'perangkat_nama',
        'teknisi_id',
        'status_barang',
        'keterangan_dismantle',
        'tanggal_dismantle',
        'foto',
    ];

    protected $casts = [
        'tanggal_dismantle' => 'datetime',
        'status_barang' => 'integer',
    ];

    public function perangkat(): BelongsTo
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_id');
    }

    public function modemDetail(): BelongsTo
    {
        return $this->belongsTo(ModemDetail::class, 'modem_detail_id');
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function customerLama(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id_lama')->withTrashed();
    }

    public function statusLabel(): string
    {
        return match ($this->status_barang) {
            14 => 'Tersedia',
            4 => 'Maintenance',
            15 => 'Rusak',
            default => 'Tidak Diketahui',
        };
    }
}
