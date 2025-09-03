<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Order;
use App\Observers\ProductObserver;
use App\Observers\OrderObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production (fix mixed content)
        if (env('FORCE_HTTPS', false) || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Configure Tailwind pagination
        Paginator::useTailwind();

        // Activer les observers (only if tables exist)
        try {
            if (Schema::hasTable('products')) {
                Product::observe(ProductObserver::class);
            }
            if (Schema::hasTable('orders')) {
                Order::observe(OrderObserver::class);
            }
        } catch (\Exception $e) {
            // Skip observers if database not ready
        }
    }
}
