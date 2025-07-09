<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration',
        'is_active',
    ];

    public function features()
    {
        return $this->belongsToMany(Feature::class)->withPivot('value')->withTimestamps();
    }
}
