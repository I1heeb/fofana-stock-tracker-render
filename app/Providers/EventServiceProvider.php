<?php

namespace App\Providers;

use App\Events\OrderStatusUpdated;
use App\Events\LowStockDetected;
use App\Listeners\HandleOrderStatusChange;
use App\Listeners\HandleLowStockNotification;
use App\Models\Order;
use App\Models\Product;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderStatusUpdated::class => [
            HandleOrderStatusChange::class,
        ],
        LowStockDetected::class => [
            HandleLowStockNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Register observers
        Order::observe(OrderObserver::class);
        Product::observe(ProductObserver::class);
    }
}