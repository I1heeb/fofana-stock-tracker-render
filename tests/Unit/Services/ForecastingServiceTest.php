<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ForecastingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastingServiceTest extends TestCase
{
    use RefreshDatabase;

    private ForecastingService $forecastingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->forecastingService = new ForecastingService();
    }

    public function test_forecast_demand_returns_array_with_correct_structure()
    {
        $product = Product::factory()->create();
        
        $forecast = $this->forecastingService->forecastDemand($product, 7);

        $this->assertIsArray($forecast);
        $this->assertCount(7, $forecast);
        $this->assertArrayHasKey('date', $forecast[0]);
        $this->assertArrayHasKey('predicted_sales', $forecast[0]);
    }

    public function test_get_restock_recommendations_identifies_low_stock()
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'minimum_stock' => 20
        ]);

        $recommendations = $this->forecastingService->getRestockRecommendations();

        $this->assertGreaterThan(0, $recommendations->count());
        $recommendation = $recommendations->first();
        $this->assertEquals($lowStockProduct->id, $recommendation['product']->id);
        $this->assertArrayHasKey('recommended_order_quantity', $recommendation);
    }

    public function test_forecast_handles_no_historical_data()
    {
        $product = Product::factory()->create();
        
        $forecast = $this->forecastingService->forecastDemand($product, 5);

        $this->assertIsArray($forecast);
        $this->assertCount(5, $forecast);
        // Should handle gracefully with no sales history
        $this->assertGreaterThanOrEqual(0, $forecast[0]['predicted_sales']);
    }
}