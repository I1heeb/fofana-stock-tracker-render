<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can access order creation page
     */
    public function test_admin_can_access_order_creation_page(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $response = $this->actingAs($admin)->get('/orders/create');

        $response->assertStatus(200);
        $response->assertViewIs('orders.create');
    }

    /**
     * Test service client cannot access order creation
     */
    public function test_service_client_cannot_access_order_creation(): void
    {
        $client = User::factory()->create([
            'role' => User::ROLE_SERVICE_CLIENT,
            'permissions' => User::getDefaultPermissions(User::ROLE_SERVICE_CLIENT)
        ]);

        $response = $this->actingAs($client)->get('/orders/create');

        $response->assertStatus(403);
    }

    /**
     * Test complete order creation workflow
     */
    public function test_complete_order_creation_workflow(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $product1 = Product::factory()->create([
            'price' => 10.00,
            'stock_quantity' => 100
        ]);

        $product2 = Product::factory()->create([
            'price' => 20.00,
            'stock_quantity' => 50
        ]);

        $orderData = [
            'products' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 1
                ]
            ],
            'notes' => 'Test order'
        ];

        $response = $this->actingAs($admin)
            ->post('/orders', $orderData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify order was created
        $this->assertDatabaseHas('orders', [
            'user_id' => $admin->id,
            'total_amount' => 50.00, // (2 * 10) + (1 * 20)
            'status' => 'in_progress'
        ]);

        // Verify order items were created
        $order = Order::where('user_id', $admin->id)->first();
        $this->assertCount(2, $order->items);
    }

    /**
     * Test order status update workflow
     */
    public function test_order_status_update_workflow(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $order = Order::factory()->create([
            'status' => Order::STATUS_IN_PROGRESS
        ]);

        $response = $this->actingAs($admin)
            ->patch("/orders/{$order->id}/status", [
                'status' => Order::STATUS_PACKED
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PACKED
        ]);
    }
}
