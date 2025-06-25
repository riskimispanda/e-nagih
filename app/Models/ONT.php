<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ONT extends Model
{
    protected $table = 'ont';
    protected $fillable = ['nama_ont', 'odp_id'];

    public function odp()
    {
        return $this->belongsTo(ODP::class, 'odp_id');
    }
}
