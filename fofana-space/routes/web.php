<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\ChartController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Reports routes - accessible by both roles
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])
        ->name('reports.dashboard')
        ->middleware('can:view-reports');
    Route::get('/reports/orders', [ReportController::class, 'orders'])
        ->name('reports.orders')
        ->middleware('can:view-reports');
    Route::get('/reports/orders/export', [ReportController::class, 'exportOrders'])
        ->name('reports.orders.export')
        ->middleware('can:view-reports');

    // Stock history routes
    Route::get('/reports/stock-history', [StockHistoryController::class, 'index'])
        ->name('reports.stock-history')
        ->middleware('can:view-reports');
    Route::get('/reports/stock-history/export', [StockHistoryController::class, 'export'])
        ->name('reports.stock-history.export')
        ->middleware('can:view-reports');

    // Chart data routes
    Route::get('/charts/kpis', [ChartController::class, 'getKpis'])
        ->name('charts.kpis')
        ->middleware('can:view-reports');
    Route::get('/charts/drilldown', [ChartController::class, 'getDrilldownData'])
        ->name('charts.drilldown')
        ->middleware('can:view-reports');
    Route::get('/charts/drilldown/export', [ChartController::class, 'exportDrilldownData'])
        ->name('charts.drilldown.export')
        ->middleware('can:view-reports');
    Route::get('/charts/orders-by-status', [ChartController::class, 'ordersByStatus'])
        ->name('charts.orders-by-status')
        ->middleware('can:view-reports');
    Route::get('/charts/stock-levels', [ChartController::class, 'stockLevels'])
        ->name('charts.stock-levels')
        ->middleware('can:view-reports');
    Route::get('/charts/low-stock-alerts', [ChartController::class, 'lowStockAlerts'])
        ->name('charts.low-stock-alerts')
        ->middleware('can:view-reports');

    // Bulk operations - packaging role only
    Route::post('/orders/bulk-update', [OrderController::class, 'bulkUpdateStatus'])
        ->name('orders.bulk-update')
        ->middleware('can:update-orders');
});
