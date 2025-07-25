<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
        
        // Register custom middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('user.level', \App\Http\Middleware\UserLevelMiddleware::class);
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        RateLimiter::for('guest-browse', function (Request $request) {
            return [
                Limit::perDay(100)->by($request->ip())->response(function () {
                    return response()->json(['message' => 'Daily thumbnail view limit reached'], 429);
                }),
                Limit::perDay(20)->by($request->ip() . ':details')->response(function () {
                    return response()->json(['message' => 'Daily detail view limit reached'], 429);
                }),
            ];
        });
        
        RateLimiter::for('user-browse', function (Request $request) {
            return Limit::perHour(600)->by($request->user()?->id)->response(function () {
                return response()->json(['message' => 'Hourly detail view limit reached'], 429);
            });
        });
    }
}