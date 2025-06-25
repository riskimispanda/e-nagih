<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Kas extends Model
{
    protected $table = 'kas';

    protected $fillable = [
        'jumlah_kas',
        'tanggal_kas',
        'kas_id',
        'keterangan',
        'debit',
        'kredit',
        'user_id'
    ];

    public function kas()
    {
        return $this->belongsTo(JenisKas::class, 'kas_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
