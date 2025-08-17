<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_decreases_when_order_status_changes_to_out()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 100]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_PACKED
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // Update status to OUT
        $order->update(['status' => Order::STATUS_OUT]);

        // Stock should be reduced
        $this->assertEquals(95, $product->fresh()->stock_quantity);
    }

    public function test_stock_restores_when_order_is_cancelled()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 95]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => Order::STATUS_OUT
        ]);
        
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // Cancel order
        $order->update(['status' => Order::STATUS_CANCELLED]);

        // Stock should be restored
        $this->assertEquals(100, $product->fresh()->stock_quantity);
    }
}