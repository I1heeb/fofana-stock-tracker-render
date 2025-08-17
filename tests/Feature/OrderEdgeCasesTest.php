<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Events\LowStockDetected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class OrderEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $packagingUser;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->packagingUser = User::factory()->packaging()->create();
        
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'stock_quantity' => 100,
            'low_stock_threshold' => 20,
        ]);
    }

    public static function invalidQuantityProvider(): array
    {
        return [
            'negative quantity' => [-1],
            'zero quantity' => [0],
            'exceeds stock' => [150],
            'decimal quantity' => [1.5],
            'string quantity' => ['invalid'],
        ];
    }

    #[DataProvider('invalidQuantityProvider')]
    #[Test]
    public function test_order_creation_fails_with_invalid_quantities(mixed $invalidQuantity): void
    {
        $this->actingAs($this->packagingUser);

        $response = $this->postJson('/orders', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $invalidQuantity,
                ]
            ]
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    #[Test]
    public function test_concurrent_orders_handle_stock_correctly(): void
    {
        $this->actingAs($this->packagingUser);

        // Create two orders simultaneously for the same product
        $orderData = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 60,
                ]
            ]
        ];

        // Create first order
        $response1 = $this->postJson('/orders', $orderData);
        $response1->assertStatus(201);

        // Try to create second order that would exceed stock
        $response2 = $this->postJson('/orders', $orderData);
        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);

        // Verify stock is correctly maintained
        $this->assertEquals(40, $this->product->fresh()->stock_quantity);
    }

    #[Test]
    public function test_low_stock_threshold_triggers_event(): void
    {
        Event::fake();
        $this->actingAs($this->packagingUser);

        // Create order that brings stock below threshold
        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 85, // Will leave 15 items (below threshold of 20)
        ]);

        // Process order to OUT status
        $order->updateStatus(Order::STATUS_PACKED);
        $order->updateStatus(Order::STATUS_OUT);

        Event::assertDispatched(LowStockDetected::class, function ($event) {
            return $event->product->id === $this->product->id
                && $event->currentStock === 15
                && $event->threshold === 20;
        });
    }

    #[Test]
    public function test_status_transitions_enforce_sequence(): void
    {
        $this->actingAs($this->packagingUser);

        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        // Try to skip PACKED status
        $response = $this->patchJson("/orders/{$order->id}", [
            'status' => Order::STATUS_OUT
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        // Verify order status hasn't changed
        $this->assertEquals(Order::STATUS_IN_PROGRESS, $order->fresh()->status);
    }

    #[Test]
    public function test_cancelled_order_restores_stock(): void
    {
        $this->actingAs($this->packagingUser);

        // Create and process order
        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 50,
        ]);

        // Process to OUT
        $order->updateStatus(Order::STATUS_PACKED);
        $order->updateStatus(Order::STATUS_OUT);

        // Verify stock reduced
        $this->assertEquals(50, $this->product->fresh()->stock_quantity);

        // Cancel order
        $order->updateStatus(Order::STATUS_CANCELED);

        // Verify stock restored
        $this->assertEquals(100, $this->product->fresh()->stock_quantity);

        // Try to update cancelled order
        $response = $this->patchJson("/orders/{$order->id}", [
            'status' => Order::STATUS_OUT
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function test_multiple_items_stock_management(): void
    {
        $this->actingAs($this->packagingUser);

        $product2 = Product::create([
            'name' => 'Test Product 2',
            'sku' => 'TEST-002',
            'stock_quantity' => 100,
            'low_stock_threshold' => 20,
        ]);

        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
        ]);

        // Add multiple items
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 30,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 40,
        ]);

        // Process to OUT
        $order->updateStatus(Order::STATUS_PACKED);
        $order->updateStatus(Order::STATUS_OUT);

        // Verify both products' stock reduced
        $this->assertEquals(70, $this->product->fresh()->stock_quantity);
        $this->assertEquals(60, $product2->fresh()->stock_quantity);

        // Cancel order
        $order->updateStatus(Order::STATUS_CANCELED);

        // Verify both products' stock restored
        $this->assertEquals(100, $this->product->fresh()->stock_quantity);
        $this->assertEquals(100, $product2->fresh()->stock_quantity);
    }
} 