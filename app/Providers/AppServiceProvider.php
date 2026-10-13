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
        Gate::define('manage-safalaw', fn (User $user) => $user->is_admin);

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
    }
}
