<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    use ApiResponses;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subscription = $request->user()->subscription;

        if (!$subscription || !$subscription->isActive()) {
            return $this->error('No active subscription', 403);
        }

        return $next($request);
    }
}
