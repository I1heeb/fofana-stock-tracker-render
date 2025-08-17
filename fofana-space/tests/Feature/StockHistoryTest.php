<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $packagingUser;
    private User $serviceClientUser;
    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packagingUser = User::factory()->create(['role' => 'packaging']);
        $this->serviceClientUser = User::factory()->create(['role' => 'service_client']);
        $this->stockService = app(StockService::class);
    }

    public function test_stock_history_is_created_on_manual_adjustment()
    {
        $this->actingAs($this->packagingUser);

        $product = Product::factory()->create(['stock' => 10]);
        $adjustment = 5;
        $notes = 'Manual restock';

        $history = $this->stockService->manualAdjustment($product, $adjustment, $notes);

        $this->assertDatabaseHas('stock_histories', [
            'id' => $history->id,
            'product_id' => $product->id,
            'user_id' => $this->packagingUser->id,
            'change' => $adjustment,
            'balance' => 15,
            'type' => 'adjustment',
            'notes' => $notes
        ]);

        $this->assertEquals(15, $product->fresh()->stock);
    }

    public function test_stock_history_is_created_on_order_status_change()
    {
        $this->actingAs($this->packagingUser);

        $product = Product::factory()->create(['stock' => 10]);
        $order = Order::factory()->create(['status' => 'packed']);
        $orderItem = $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $this->stockService->handleOrderStatusChange($order, 'packed', 'out');

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $product->id,
            'user_id' => $this->packagingUser->id,
            'order_id' => $order->id,
            'change' => -3,
            'balance' => 7,
            'type' => 'order_out'
        ]);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_stock_history_is_created_on_order_return()
    {
        $this->actingAs($this->packagingUser);

        $product = Product::factory()->create(['stock' => 7]);
        $order = Order::factory()->create(['status' => 'out']);
        $orderItem = $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $this->stockService->handleOrderStatusChange($order, 'out', 'returned');

        $this->assertDatabaseHas('stock_histories', [
            'product_id' => $product->id,
            'user_id' => $this->packagingUser->id,
            'order_id' => $order->id,
            'change' => 3,
            'balance' => 10,
            'type' => 'order_return'
        ]);

        $this->assertEquals(10, $product->fresh()->stock);
    }

    public function test_stock_history_report_filters()
    {
        $this->actingAs($this->packagingUser);

        $product1 = Product::factory()->create(['stock' => 10]);
        $product2 = Product::factory()->create(['stock' => 20]);

        // Create some history records
        $this->stockService->manualAdjustment($product1, 5, 'Restock product 1');
        $this->stockService->manualAdjustment($product2, -3, 'Remove damaged items');

        // Test product filter
        $response = $this->get(route('reports.stock-history', ['product_id' => $product1->id]));
        $response->assertOk();
        $response->assertViewHas('history', function ($history) use ($product1) {
            return $history->count() === 1 &&
                   $history->first()->product_id === $product1->id;
        });

        // Test type filter
        $response = $this->get(route('reports.stock-history', ['type' => 'adjustment']));
        $response->assertOk();
        $response->assertViewHas('history', function ($history) {
            return $history->count() === 2 &&
                   $history->every(fn ($record) => $record->type === 'adjustment');
        });

        // Test date filter
        $response = $this->get(route('reports.stock-history', [
            'from' => now()->subDay()->format('Y-m-d'),
            'to' => now()->format('Y-m-d')
        ]));
        $response->assertOk();
        $response->assertViewHas('history', function ($history) {
            return $history->count() === 2;
        });
    }

    public function test_stock_history_export()
    {
        $this->actingAs($this->packagingUser);

        $product = Product::factory()->create(['stock' => 10]);
        $this->stockService->manualAdjustment($product, 5, 'Test adjustment');

        $response = $this->get(route('reports.stock-history.export'));
        
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="stock_history_' . now()->format('Y-m-d') . '*.csv"', true);
    }

    public function test_prevents_negative_stock()
    {
        $this->actingAs($this->packagingUser);

        $product = Product::factory()->create(['stock' => 5]);

        $this->expectException(\InvalidArgumentException::class);
        $this->stockService->manualAdjustment($product, -10, 'This should fail');

        $this->assertEquals(5, $product->fresh()->stock);
        $this->assertDatabaseMissing('stock_histories', [
            'product_id' => $product->id,
            'change' => -10
        ]);
    }
} 