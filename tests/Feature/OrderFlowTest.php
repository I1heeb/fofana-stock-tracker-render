<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $packagingUser;
    private User $serviceClientUser;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->packagingUser = User::factory()->packaging()->create();
        $this->serviceClientUser = User::factory()->serviceClient()->create();

        // Create a test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'stock_quantity' => 100,
            'minimum_stock_level' => 10,
        ]);
    }

    #[Test]
    public function packaging_user_can_create_and_manage_orders(): void
    {
        $this->actingAs($this->packagingUser);

        // Create a new order
        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'notes' => 'Test order',
        ]);

        // Add order items
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'notes' => 'Test order item',
        ]);

        // Load relationships
        $order->load('orderItems.product');

        // Verify relationships
        $this->assertEquals($order->id, $orderItem->order->id);
        $this->assertEquals($this->product->id, $orderItem->product->id);
        $this->assertEquals(5, $orderItem->quantity);

        // Test order status workflow
        $order->updateStatus(Order::STATUS_PACKED);
        $this->assertEquals(Order::STATUS_PACKED, $order->fresh()->status);

        $order->updateStatus(Order::STATUS_OUT);
        $this->assertEquals(Order::STATUS_OUT, $order->fresh()->status);

        // Verify stock adjustment
        $this->assertEquals(95, $this->product->fresh()->stock_quantity);

        // Test logging
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->packagingUser->id,
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'action' => 'stock_adjustment',
        ]);
    }

    #[Test]
    public function service_client_user_can_only_view_orders(): void
    {
        $this->actingAs($this->serviceClientUser);

        // Create an order as packaging user
        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'notes' => 'Test order',
        ]);

        // Service client should be able to view the order
        $response = $this->get("/orders/{$order->id}");
        $response->assertStatus(200);

        // Service client should not be able to update the order
        $response = $this->patch("/orders/{$order->id}", [
            'status' => Order::STATUS_PACKED,
        ]);
        $response->assertStatus(403);

        // Verify order status hasn't changed
        $this->assertEquals(Order::STATUS_IN_PROGRESS, $order->fresh()->status);
    }

    #[Test]
    public function test_order_cancellation_and_return_flow(): void
    {
        $this->actingAs($this->packagingUser);

        // Create and process an order
        $order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'notes' => 'Test order',
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'notes' => 'Test order item',
        ]);

        // Load relationships
        $order->load('orderItems.product');

        // Process order to OUT status
        $order->updateStatus(Order::STATUS_PACKED);
        $order->updateStatus(Order::STATUS_OUT);

        // Initial stock should be reduced
        $this->assertEquals(95, $this->product->fresh()->stock_quantity);

        // Cancel order
        $order->updateStatus(Order::STATUS_CANCELED);

        // Stock should be restored
        $this->assertEquals(100, $this->product->fresh()->stock_quantity);

        // Verify logs for cancellation
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->packagingUser->id,
            'order_id' => $order->id,
            'action' => 'order_status_changed',
            'old_value' => json_encode(['status' => Order::STATUS_OUT]),
            'new_value' => json_encode(['status' => Order::STATUS_CANCELED]),
        ]);
    }
} 