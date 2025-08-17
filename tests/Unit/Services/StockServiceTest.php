<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stockService = new StockService();
    }

    public function test_adjust_stock_increases_quantity()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->stockService->adjustStock($product, 5, 'restock', 'Test restock');

        $this->assertEquals(15, $product->fresh()->stock_quantity);
        $this->assertEquals(5, $result->change);
        $this->assertEquals('restock', $result->type);
    }

    public function test_adjust_stock_prevents_negative_stock()
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->expectException(\InvalidArgumentException::class);
        $this->stockService->adjustStock($product, -10, 'sale', 'Test sale');
    }

    public function test_manual_adjustment_creates_log()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->stockService->manualAdjustment($product, 3, 'Manual count adjustment');

        $this->assertEquals('adjustment', $result->type);
        $this->assertEquals('Manual count adjustment', $result->notes);
        $this->assertEquals(13, $product->fresh()->stock_quantity);
    }
}