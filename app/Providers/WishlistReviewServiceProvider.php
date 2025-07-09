<?php

namespace App\Providers;

use App\Services\ReviewService;
use App\Services\WishlistService;
use Illuminate\Support\ServiceProvider;

class WishlistReviewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(WishlistService::class, function ($app) {
            return new WishlistService();
        });

        $this->app->singleton(ReviewService::class, function ($app) {
            return new ReviewService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
