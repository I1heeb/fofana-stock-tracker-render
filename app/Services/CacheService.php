<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    const DASHBOARD_CACHE_KEY = 'dashboard_stats';
    const CACHE_TTL = 300; // 5 minutes

    public static function clearDashboardCache()
    {
        Cache::forget(self::DASHBOARD_CACHE_KEY);
        Cache::forget('dashboard.total_orders');
        Cache::forget('dashboard.pending_orders');
        Cache::forget('dashboard.low_stock_products');
        Cache::forget('dashboard.recent_activities');
    }

    public static function clearProductCache()
    {
        Cache::forget('products.all');
        Cache::forget('products.available_for_orders');
        self::clearDashboardCache();
    }

    public static function clearOrderCache()
    {
        Cache::forget('orders.paginated');
        self::clearDashboardCache();
    }

    public static function refreshDashboardCache()
    {
        self::clearDashboardCache();
        // Pre-warm the cache
        app(\App\Http\Controllers\DashboardController::class)->getDashboardStats();
    }
}







