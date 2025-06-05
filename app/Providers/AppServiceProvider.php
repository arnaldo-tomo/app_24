<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\PushNotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(PushNotificationService::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        // Order::observe(OrderObserver::class);
    }
}
