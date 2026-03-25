<?php

namespace App\Providers;

use App\Services\WalletService;
use App\Services\BookingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WalletService::class, function () {
            return new WalletService();
        });

        $this->app->singleton(BookingService::class, function ($app) {
            return new BookingService(
                $app->make(WalletService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}