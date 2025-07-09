<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureUsage extends Model
{
    protected $fillable = ['subscription_id', 'feature_id', 'used'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }
}
