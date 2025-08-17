<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test order status constants
     */
    public function test_order_status_constants_are_defined(): void
    {
        $this->assertEquals('in_progress', Order::STATUS_IN_PROGRESS);
        $this->assertEquals('packed', Order::STATUS_PACKED);
        $this->assertEquals('out', Order::STATUS_OUT);
        $this->assertEquals('cancelled', Order::STATUS_CANCELLED);
        $this->assertEquals('returned', Order::STATUS_RETURNED);
    }

    /**
     * Test order statuses method returns correct array
     */
    public function test_get_statuses_returns_correct_array(): void
    {
        $statuses = Order::getStatuses();

        $this->assertIsArray($statuses);
        $this->assertArrayHasKey('in_progress', $statuses);
        $this->assertArrayHasKey('packed', $statuses);
        $this->assertArrayHasKey('out', $statuses);
        $this->assertArrayHasKey('cancelled', $statuses);
        $this->assertArrayHasKey('returned', $statuses);
    }

    /**
     * Test order creation with required fields
     */
    public function test_order_can_be_created_with_required_fields(): void
    {
        $user = User::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-001',
            'status' => Order::STATUS_IN_PROGRESS,
            'total_amount' => 199.99
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'order_number' => 'ORD-001',
            'status' => 'in_progress',
            'total_amount' => 199.99
        ]);
    }

    /**
     * Test order belongs to user relationship
     */
    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /**
     * Test order has many order items relationship
     */
    public function test_order_has_many_order_items(): void
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $order->items());
    }

    /**
     * Test order number generation is unique
     */
    public function test_order_number_is_unique(): void
    {
        $order1 = Order::factory()->create(['order_number' => 'ORD-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Order::factory()->create(['order_number' => 'ORD-001']);
    }

    /**
     * Test order total amount calculation
     */
    public function test_order_total_amount_calculation(): void
    {
        $order = Order::factory()->create(['total_amount' => 0]);

        // This would typically be calculated from order items
        // For now, we test that the field accepts decimal values
        $order->update(['total_amount' => 299.99]);

        $this->assertEquals(299.99, $order->fresh()->total_amount);
    }
}
