<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $table = 'pengadaan';

    protected $fillable = [
        'nama_barang',
        'perangkat_id',
        'kategori_id',
        'jenis_pengadaan',
        'jumlah',
        'harga_satuan',
        'total_harga',
        'keterangan',
        'alasan_tolak',
        'user_id',
        'approved_by',
        'status_id',
        'rab_id',
        'pengeluaran_id',
        'tanggal_permintaan',
        'tanggal_disetujui',
        'tanggal_diterima',
        'bukti_pembelian',
    ];

    protected $casts = [
        'tanggal_permintaan' => 'datetime',
        'tanggal_disetujui' => 'datetime',
        'tanggal_diterima' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function rab()
    {
        return $this->belongsTo(Rab::class, 'rab_id');
    }

    public function perangkat()
    {
        return $this->belongsTo(Perangkat::class, 'perangkat_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriLogistik::class, 'kategori_id');
    }

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_id');
    }
}
