<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router; // Import the Router facade
use App\Http\Middleware\RoleMiddleware; // Import your custom middleware

class MiddlewareServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        // ✅ FIX: Use the Router service to explicitly alias the middleware.
        // This is the most reliable way to register route middleware aliases.
        $router->aliasMiddleware('role', RoleMiddleware::class);
    }
}