<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JPengaduan extends Model
{
    protected $table = 'j_pengaduan';
    protected $fillable = ['jenis_pengaduan'];

    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'pengaduan_id');
    }
}
