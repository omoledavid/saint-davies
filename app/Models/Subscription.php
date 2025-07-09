<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = ['user_id', 'plan_id', 'starts_at', 'ends_at', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->is_active && now()->between($this->starts_at, $this->ends_at);
    }
    public function featureUsages()
    {
        return $this->hasMany(FeatureUsage::class);
    }

    public function getFeatureValue($code)
    {
        $feature = $this->plan->features->where('code', $code)->first();
        return $feature ? $feature->pivot->value : null;
    }

    public function getUsedFeatureValue($code)
    {
        $feature = Feature::where('code', $code)->first();
        return $this->featureUsages()->where('feature_id', $feature->id)->value('used');
    }
}
