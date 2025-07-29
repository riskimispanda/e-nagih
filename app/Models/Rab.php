<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    protected $table = 'rab';
    protected $fillable = ['jumlah_anggaran','tahun_anggaran','user_id','kegiatan','bulan','item','status_id'];

    public function usr()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'rab_id');
    }

}
