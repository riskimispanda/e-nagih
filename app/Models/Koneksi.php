<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Koneksi extends Model
{
    protected $table = 'koneksi';

    protected $fillable = [
        'nama_koneksi',
    ];
}
