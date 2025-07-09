<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use ApiResponses;
    public function subscribe(Request $request)
    {
        $user = auth()->user();
        $plan = Plan::find($request->plan_id);
        if (!$plan) {
            return $this->error('Plan not found', 404);
        }

        $now = now();
        $endsAt = $now->copy()->addDays($plan->duration);

        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'starts_at' => $now,
                'ends_at' => $endsAt,
                'is_active' => true,
            ]
        );

        return $this->ok('Subscribed successfully', $subscription);
    }

    public function current()
    {
        return $this->ok('Current subscription', auth()->user()->subscription()->with('plan')->first());
    }
}

