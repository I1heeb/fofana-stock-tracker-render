<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\BarcodeController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Dashboard API routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/recent-orders', [DashboardController::class, 'getRecentOrdersApi']);
    Route::get('/dashboard/stats', [DashboardController::class, 'getStatsApi']);
});

// Add these API routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Barcode scanning
    Route::post('barcode/scan', [BarcodeController::class, 'scan']);
    Route::post('barcode/generate', [BarcodeController::class, 'generate']);
    

});

