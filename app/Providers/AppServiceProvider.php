<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiterFacade::for('api', function (Request $request) {
            return ThrottleRequests::with(
                maxAttempts: 60,
                decayMinutes: 1,
                responseCallback: fn () => response()->json([
                    'status' => 'error',
                    'message' => 'Too many requests. Please try again later.',
                ], 429)
            )->by(
                $request->user()?->id ?? $request->ip()
            );
        });
    }
}
