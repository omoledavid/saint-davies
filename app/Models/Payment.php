<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'tenant_id',
        'amount',
        'type',
        'method',
        'reference',
        'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }
}
