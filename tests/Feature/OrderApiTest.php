<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_api_orders_index_requires_authentication()
    {
        $response = $this->getJson('/api/orders');
        $response->assertStatus(401);
    }

    public function test_api_orders_index_returns_paginated_orders()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        Sanctum::actingAs($user);
        
        Order::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/orders');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user_id', 'status', 'created_at']
                ],
                'links',
                'meta'
            ]);
    }

    public function test_api_order_creation_validates_input()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => []
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_api_order_creation_success()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'user_id', 'status', 'order_items'
            ]);
    }

    public function test_api_order_status_update()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->putJson("/api/orders/{$order->id}/status", [
            'status' => 'packed'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order status updated successfully'
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'packed'
        ]);
    }
}