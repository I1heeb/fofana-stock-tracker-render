<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;
    private User $packagingUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'sku' => 'TEST-001',
            'stock_quantity' => 15,
            'low_stock_threshold' => 20,
        ]);

        $this->packagingUser = User::factory()->packaging()->create();
    }

    #[Test]
    public function test_low_stock_notification_contains_correct_data(): void
    {
        $notification = new LowStockAlert(
            $this->product,
            15,
            20
        );

        $mailData = $notification->toMail($this->packagingUser);
        $arrayData = $notification->toArray($this->packagingUser);

        // Test mail notification content
        $this->assertEquals('Low Stock Alert: Test Product', $mailData->subject);
        $this->assertStringContainsString('Current stock: 15', implode(' ', $mailData->introLines));
        $this->assertStringContainsString('Threshold: 20', implode(' ', $mailData->introLines));
        $this->assertStringContainsString('SKU: TEST-001', implode(' ', $mailData->introLines));

        // Test array representation
        $this->assertEquals([
            'product_id' => $this->product->id,
            'product_name' => 'Test Product',
            'current_stock' => 15,
            'threshold' => 20,
            'sku' => 'TEST-001',
        ], $arrayData);
    }

    #[Test]
    public function test_notification_sent_to_all_packaging_users(): void
    {
        Notification::fake();

        // Create additional packaging users
        $packagingUser2 = User::factory()->packaging()->create();
        $packagingUser3 = User::factory()->packaging()->create();
        
        // Create a service client user (shouldn't receive notification)
        $serviceUser = User::factory()->serviceClient()->create();

        // Dispatch notification
        Notification::send(
            User::where('role', 'packaging')->get(),
            new LowStockAlert($this->product, 15, 20)
        );

        // Verify notifications were sent to packaging users only
        Notification::assertSentTo(
            [$this->packagingUser, $packagingUser2, $packagingUser3],
            LowStockAlert::class
        );

        Notification::assertNotSentTo(
            [$serviceUser],
            LowStockAlert::class
        );
    }

    #[Test]
    public function test_notification_channels(): void
    {
        $notification = new LowStockAlert(
            $this->product,
            15,
            20
        );

        $this->assertEquals(['mail', 'database'], $notification->via($this->packagingUser));
    }

    #[Test]
    public function test_notification_database_storage(): void
    {
        // Send notification
        $this->packagingUser->notify(
            new LowStockAlert($this->product, 15, 20)
        );

        // Check if notification was stored in database
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $this->packagingUser->id,
            'type' => LowStockAlert::class,
        ]);

        // Verify notification data
        $notification = $this->packagingUser->notifications()->first();
        $data = $notification->data;

        $this->assertEquals($this->product->id, $data['product_id']);
        $this->assertEquals('Test Product', $data['product_name']);
        $this->assertEquals(15, $data['current_stock']);
        $this->assertEquals(20, $data['threshold']);
        $this->assertEquals('TEST-001', $data['sku']);
    }
} 