<?php

namespace App\Providers;

use App\Services\ZohoMailService;
use App\Channels\ZohoMailChannel;
use Illuminate\Support\ServiceProvider;

class ZohoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ZohoMailService::class, function ($app) {
            return new ZohoMailService();
        });

        $this->app->singleton(ZohoMailChannel::class, function ($app) {
            return new ZohoMailChannel($app->make(ZohoMailService::class));
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
