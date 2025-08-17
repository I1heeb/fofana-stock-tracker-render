<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ForecastingService
{
    public function forecastDemand(Product $product, int $days = 30): array
    {
        $historicalData = $this->getHistoricalSales($product, 90);
        $averageDailySales = $this->calculateAverageDailySales($historicalData);
        $trend = $this->calculateTrend($historicalData);
        
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = Carbon::now()->addDays($i);
            $predictedSales = $averageDailySales + ($trend * $i);
            $forecast[] = [
                'date' => $date->format('Y-m-d'),
                'predicted_sales' => max(0, round($predictedSales, 2)),
            ];
        }
        
        return $forecast;
    }

    public function getRestockRecommendations(): Collection
    {
        return Product::with('supplier')
            ->where('stock_quantity', '<=', \DB::raw('low_stock_threshold'))
            ->get()
            ->map(function ($product) {
                $forecast = $this->forecastDemand($product, 30);
                $totalPredictedSales = array_sum(array_column($forecast, 'predicted_sales'));
                
                return [
                    'product' => $product,
                    'current_stock' => $product->stock_quantity,
                    'predicted_30_day_sales' => $totalPredictedSales,
                    'recommended_order_quantity' => max(
                        $product->minimum_stock,
                        $totalPredictedSales + $product->minimum_stock - $product->stock_quantity
                    ),
                    'days_until_stockout' => $this->calculateDaysUntilStockout($product),
                ];
            });
    }

    private function getHistoricalSales(Product $product, int $days): Collection
    {
        return Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.product_id', $product->id)
            ->where('orders.created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(orders.created_at) as date, SUM(order_items.quantity) as quantity')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function calculateAverageDailySales(Collection $historicalData): float
    {
        if ($historicalData->isEmpty()) {
            return 0;
        }
        
        return $historicalData->avg('quantity');
    }

    private function calculateTrend(Collection $historicalData): float
    {
        if ($historicalData->count() < 2) {
            return 0;
        }
        
        $data = $historicalData->values();
        $n = $data->count();
        $sumX = $n * ($n - 1) / 2;
        $sumY = $data->sum('quantity');
        $sumXY = $data->sum(fn($item, $index) => $index * $item['quantity']);
        $sumX2 = $n * ($n - 1) * (2 * $n - 1) / 6;
        
        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    }

    private function calculateDaysUntilStockout(Product $product): int
    {
        $historicalData = $this->getHistoricalSales($product, 30);
        $averageDailySales = $this->calculateAverageDailySales($historicalData);
        
        if ($averageDailySales <= 0) {
            return 999; // Infinite days if no sales
        }
        
        return (int) ceil($product->stock_quantity / $averageDailySales);
    }
}