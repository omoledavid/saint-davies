<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'tenancy_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    public function tenancy()
    {
        return $this->belongsTo(Tenancy::class);
    }
}
