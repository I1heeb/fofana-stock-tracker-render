<?php

namespace Tests\Feature;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BulkOrderOperationsTest extends TestCase
{
    use RefreshDatabase;

    private User $packagingUser;
    private User $serviceClientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packagingUser = User::factory()->create(['role' => 'packaging']);
        $this->serviceClientUser = User::factory()->create(['role' => 'service_client']);
    }

    public function test_packaging_user_can_bulk_update_orders()
    {
        Event::fake();

        $orders = Order::factory()->count(3)->create(['status' => 'in_progress']);

        $response = $this->actingAs($this->packagingUser)
            ->postJson(route('orders.bulk-update'), [
                'order_ids' => $orders->pluck('id')->toArray(),
                'status' => 'packed'
            ]);

        $response->assertOk()
            ->assertJson([
                'success_count' => 3,
                'failed_orders' => []
            ]);

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'packed'
            ]);

            Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
                return $event->order->id === $order->id &&
                    $event->oldStatus === 'in_progress' &&
                    $event->order->status === 'packed';
            });
        }
    }

    public function test_service_client_cannot_bulk_update_orders()
    {
        $orders = Order::factory()->count(3)->create(['status' => 'in_progress']);

        $response = $this->actingAs($this->serviceClientUser)
            ->postJson(route('orders.bulk-update'), [
                'order_ids' => $orders->pluck('id')->toArray(),
                'status' => 'packed'
            ]);

        $response->assertForbidden();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'in_progress'
            ]);
        }
    }

    public function test_validates_invalid_status_transitions()
    {
        $orders = [
            Order::factory()->create(['status' => 'in_progress']),
            Order::factory()->create(['status' => 'out']),
            Order::factory()->create(['status' => 'packed'])
        ];

        $response = $this->actingAs($this->packagingUser)
            ->postJson(route('orders.bulk-update'), [
                'order_ids' => collect($orders)->pluck('id')->toArray(),
                'status' => 'packed'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_ids']);

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => $order->status
            ]);
        }
    }

    public function test_can_cancel_orders_from_any_status()
    {
        Event::fake();

        $orders = [
            Order::factory()->create(['status' => 'in_progress']),
            Order::factory()->create(['status' => 'out']),
            Order::factory()->create(['status' => 'packed'])
        ];

        $response = $this->actingAs($this->packagingUser)
            ->postJson(route('orders.bulk-update'), [
                'order_ids' => collect($orders)->pluck('id')->toArray(),
                'status' => 'canceled'
            ]);

        $response->assertOk();

        foreach ($orders as $order) {
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'canceled'
            ]);

            Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
                return $event->order->id === $order->id &&
                    $event->oldStatus === $order->status &&
                    $event->order->status === 'canceled';
            });
        }
    }

    public function test_handles_nonexistent_orders()
    {
        $response = $this->actingAs($this->packagingUser)
            ->postJson(route('orders.bulk-update'), [
                'order_ids' => [999999],
                'status' => 'packed'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_ids.0']);
    }
} 