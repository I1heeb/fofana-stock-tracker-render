<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class BordereauValidationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user with packaging_agent role
        $this->user = User::factory()->create([
            'role' => 'packaging_agent',
            'permissions' => ['orders.create', 'products.view']
        ]);
        
        // Create a test product
        $this->product = Product::factory()->create([
            'stock_quantity' => 10,
            'price' => 25.99
        ]);
    }

    /**
     * Test that order creation fails without bordereau number
     */
    public function test_order_creation_fails_without_bordereau_number(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/orders', [
            'notes' => 'Test order without bordereau',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['bordereau_number']);
        $this->assertDatabaseMissing('orders', [
            'notes' => 'Test order without bordereau'
        ]);
    }

    /**
     * Test that order creation fails with invalid bordereau number (too short)
     */
    public function test_order_creation_fails_with_short_bordereau_number(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/orders', [
            'bordereau_number' => '12345', // Only 5 digits
            'notes' => 'Test order with short bordereau',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['bordereau_number']);
        $this->assertDatabaseMissing('orders', [
            'bordereau_number' => '12345'
        ]);
    }

    /**
     * Test that order creation fails with invalid bordereau number (too long)
     */
    public function test_order_creation_fails_with_long_bordereau_number(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/orders', [
            'bordereau_number' => '1234567890123', // 13 digits
            'notes' => 'Test order with long bordereau',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['bordereau_number']);
        $this->assertDatabaseMissing('orders', [
            'bordereau_number' => '1234567890123'
        ]);
    }

    /**
     * Test that order creation fails with non-numeric bordereau number
     */
    public function test_order_creation_fails_with_non_numeric_bordereau_number(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/orders', [
            'bordereau_number' => 'ABC123456789', // Contains letters
            'notes' => 'Test order with non-numeric bordereau',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertSessionHasErrors(['bordereau_number']);
        $this->assertDatabaseMissing('orders', [
            'bordereau_number' => 'ABC123456789'
        ]);
    }

    /**
     * Test that order creation succeeds with valid 12-digit bordereau number
     */
    public function test_order_creation_succeeds_with_valid_bordereau_number(): void
    {
        $this->actingAs($this->user);

        $validBordereau = '123456789012'; // Exactly 12 digits

        $response = $this->post('/orders', [
            'bordereau_number' => $validBordereau,
            'notes' => 'Test order with valid bordereau',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('orders', [
            'bordereau_number' => $validBordereau,
            'user_id' => $this->user->id
        ]);
    }

    /**
     * Test that bordereau number is stored correctly in database
     */
    public function test_bordereau_number_is_stored_correctly(): void
    {
        $this->actingAs($this->user);

        $bordereau = '987654321098';

        $this->post('/orders', [
            'bordereau_number' => $bordereau,
            'notes' => 'Test bordereau storage',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ]
        ]);

        $order = Order::where('bordereau_number', $bordereau)->first();
        
        $this->assertNotNull($order);
        $this->assertEquals($bordereau, $order->bordereau_number);
        $this->assertEquals(12, strlen($order->bordereau_number));
        $this->assertTrue(ctype_digit($order->bordereau_number));
    }

    /**
     * Test that bordereau number search works correctly
     */
    public function test_bordereau_number_search_works(): void
    {
        $this->actingAs($this->user);

        // Create orders with different bordereau numbers
        $bordereau1 = '111111111111';
        $bordereau2 = '222222222222';
        $bordereau3 = '333333333333';

        foreach ([$bordereau1, $bordereau2, $bordereau3] as $bordereau) {
            $this->post('/orders', [
                'bordereau_number' => $bordereau,
                'notes' => "Order with bordereau {$bordereau}",
                'items' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1
                    ]
                ]
            ]);
        }

        // Test full bordereau search
        $response = $this->get('/orders?bordereau_search=' . $bordereau1);
        $response->assertSee($bordereau1);
        $response->assertDontSee($bordereau2);
        $response->assertDontSee($bordereau3);

        // Test partial bordereau search
        $response = $this->get('/orders?bordereau_search=1111');
        $response->assertSee($bordereau1);
        $response->assertDontSee($bordereau2);
        $response->assertDontSee($bordereau3);
    }

    /**
     * Test that service client cannot create orders (even with valid bordereau)
     */
    public function test_service_client_cannot_create_orders(): void
    {
        $serviceClient = User::factory()->create([
            'role' => 'service_client'
        ]);

        $this->actingAs($serviceClient);

        $response = $this->post('/orders', [
            'bordereau_number' => '123456789012',
            'notes' => 'Service client attempt',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('orders', [
            'bordereau_number' => '123456789012'
        ]);
    }

    /**
     * Test that bordereau numbers are unique (if we want to enforce uniqueness)
     */
    public function test_duplicate_bordereau_numbers_are_allowed(): void
    {
        $this->actingAs($this->user);

        $bordereau = '555555555555';

        // Create first order
        $this->post('/orders', [
            'bordereau_number' => $bordereau,
            'notes' => 'First order',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ]
        ]);

        // Create second order with same bordereau (should be allowed for now)
        $response = $this->post('/orders', [
            'bordereau_number' => $bordereau,
            'notes' => 'Second order',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1
                ]
            ]
        ]);

        $response->assertRedirect();
        
        // Both orders should exist
        $this->assertEquals(2, Order::where('bordereau_number', $bordereau)->count());
    }
}
