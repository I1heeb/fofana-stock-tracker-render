<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
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
        // Configure Tailwind pagination
        Paginator::useTailwind();
        
        // Activer les observers
        Product::observe(ProductObserver::class);
        Order::observe(OrderObserver::class);
    }
}
