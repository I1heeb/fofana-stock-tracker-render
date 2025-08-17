<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class OrderCreateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_order_creation_page_loads_with_products()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $products = Product::factory()->count(3)->create(['stock_quantity' => 10]);

        $response = $this->actingAs($user)
            ->get(route('orders.create'));

        $response->assertStatus(200)
            ->assertViewIs('orders.create')
            ->assertViewHas('products');
    }

    public function test_order_creation_deducts_stock_and_logs()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 25.50
        ]);

        $response = $this->actingAs($user)
            ->post(route('orders.store'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3]
                ]
            ]);

        $response->assertRedirect(route('orders.index'))
            ->assertSessionHas('success');

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => Order::STATUS_IN_PROGRESS
        ]);

        // Verify order item was created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => 25.50
        ]);

        // Verify log was created
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'order_status_changed'
        ]);
    }

    public function test_insufficient_stock_prevents_order_creation()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 2]);

        $response = $this->actingAs($user)
            ->post(route('orders.store'), [
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 5]
                ]
            ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_status_update_adjusts_stock()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_IN_PROGRESS
        ]);
        
        $order->orderItems()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'price' => $product->price
        ]);

        // Update order to 'out' status
        $response = $this->actingAs($user)
            ->put(route('orders.update', $order), [
                'status' => Order::STATUS_OUT
            ]);

        $response->assertRedirect(route('orders.index'));
        
        // Verify stock was reduced
        $product->refresh();
        $this->assertEquals(7, $product->stock_quantity);
        
        // Verify log was created
        $this->assertDatabaseHas('logs', [
            'order_id' => $order->id,
            'action' => 'order_status_changed'
        ]);
    }
}