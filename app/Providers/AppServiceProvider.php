<?php

namespace App\Providers;

use App\InfrastructureServices\AddInfrastructureService;
use App\InfrastructureServices\DependencyInjection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //if there is a key from env to inject
        // $this->app->bind(AuthServiceInterface::class, function ($app) {
        //     return new AuthService(config('services.auth_service.api_key'));
        // });

        DependencyInjection::AppServices($this->app);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
