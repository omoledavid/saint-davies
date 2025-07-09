<?php

namespace App\Services;

use App\Models\Feature;
use App\Models\FeatureUsage;
use App\Models\User;

class SubscriptionService
{
    public function canUseFeature(User $user, string $featureCode): bool
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->isActive()) return false;

        $limit = $subscription->getFeatureValue($featureCode);
        if (!$limit || $limit === 'unlimited') return true;

        $used = $subscription->getUsedFeatureValue($featureCode);
        return $used < (int) $limit;
    }

    public function incrementUsage(User $user, string $featureCode, int $amount = 1): void
    {
        $subscription = $user->subscription;
        $feature = Feature::where('code', $featureCode)->first();

        $usage = FeatureUsage::firstOrCreate(
            ['subscription_id' => $subscription->id, 'feature_id' => $feature->id],
            ['used' => 0]
        );

        $usage->increment('used', $amount);
    }
}
