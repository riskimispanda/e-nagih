<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedBlockLog extends Model
{
    protected $table = 'failed_block_logs';

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'router_id',
        'error_type',
        'error_message',
        'error_detail',
        'source',
        'usersecret',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function router()
    {
        return $this->belongsTo(Router::class, 'router_id');
    }
}
