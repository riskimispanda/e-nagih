<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsLog extends Model
{
        use HasFactory;

        /**
         * Nama tabel yang terhubung dengan model ini.
         *
         * @var string
         */
        protected $table = 'whats_log';

        /**
         * Field/Kolom yang diizinkan untuk diisi secara massal (Mass Assignment).
         *
         * @var array<int, string>
         */
        protected $fillable = [
                'customer_id',
                'pesan',
                'jenis_pesan',
                'qontak_broadcast_id',
                'status_pengiriman',
                'no_tujuan',
                'error_message',
        ];

        /**
         * Relasi ke tabel customer (Satu Log dimiliki oleh Satu Customer).
         */
        public function customer()
        {
                return $this->belongsTo(Customer::class, 'customer_id');
        }
}
