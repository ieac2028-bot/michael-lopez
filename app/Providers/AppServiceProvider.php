<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::define('manage-safalaw', fn (User $user) => $user->isAdministrator());
        Gate::define('access-client-portal', fn (User $user) => $user->isClient());
        Gate::define('access-attorney-portal', fn (User $user) => $user->isAttorney());
        Gate::define('verify-safalaw-account', fn (User $user) => $user->isClient() || $user->isAttorney());

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)
                ->by('admin-login:'.$request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfter = (int) ($headers['Retry-After'] ?? 60);
                    $message = "Too many login attempts. Try again in {$retryAfter} seconds.";

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    return response($message, 429, $headers)
                        ->header('Content-Type', 'text/plain; charset=UTF-8');
                });
        });

        RateLimiter::for('client-login', fn (Request $request) =>
            Limit::perMinute(5)->by('client-login:'.$request->ip())
        );

        RateLimiter::for('client-register', fn (Request $request) =>
            Limit::perHour(5)->by('client-register:'.$request->ip())
        );

        RateLimiter::for('client-portal-lookup', fn (Request $request) =>
            Limit::perMinute(10)->by('client-case-lookup:'.$request->user()?->id.':'.$request->ip())
        );

        RateLimiter::for('attorney-login', fn (Request $request) =>
            Limit::perMinute(5)->by('attorney-login:'.$request->ip())
        );

        RateLimiter::for('password-email', fn (Request $request) =>
            Limit::perMinute(5)->by('password-email:'.$request->ip())
        );

        RateLimiter::for('password-reset', fn (Request $request) =>
            Limit::perMinute(5)->by('password-reset:'.$request->ip())
        );
    }
}
