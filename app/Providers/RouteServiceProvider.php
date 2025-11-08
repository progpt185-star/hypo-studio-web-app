<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $routes = $this->app->basePath('routes/web.php');

        if (file_exists($routes)) {
            // Load the routes file inside the "web" middleware group so session,
            // CSRF and shared view data (like $errors) are available to routes.
            $router = $this->app->make(\Illuminate\Routing\Router::class);
            $router->group(['middleware' => 'web'], function () use ($routes) {
                require $routes;
            });
        }
    }
}
