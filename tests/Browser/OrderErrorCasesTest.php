<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderErrorCasesTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $packagingUser;
    protected User $serviceUser;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create users with different roles
        $this->packagingUser = User::factory()->create([
            'role' => 'packaging',
            'email' => 'packer@fofana.test',
            'password' => bcrypt('secret'),
        ]);

        $this->serviceUser = User::factory()->create([
            'role' => 'service_client',
            'email' => 'service@fofana.test',
            'password' => bcrypt('secret'),
        ]);

        // Create test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'stock_quantity' => 10,
            'low_stock_threshold' => 5,
            'sku' => 'TEST-001',
        ]);

        // Create a test order
        $this->order = Order::create([
            'user_id' => $this->packagingUser->id,
            'status' => Order::STATUS_IN_PROGRESS,
            'notes' => 'Test order',
        ]);
    }

    public function test_service_client_cannot_modify_orders()
    {
        $this->browse(function (Browser $browser) {
            // Login as service client
            $browser->visit('/login')
                   ->type('email', $this->serviceUser->email)
                   ->type('password', 'secret')
                   ->press('Log in')
                   ->assertPathIs('/dashboard');

            // Try to create order
            $browser->visit('/orders/create')
                   ->assertSee('Unauthorized')
                   ->assertPathIsNot('/orders/create');

            // Try to modify existing order
            $browser->visit("/orders/{$this->order->id}/edit")
                   ->assertSee('Unauthorized')
                   ->assertPathIsNot("/orders/{$this->order->id}/edit");
        });
    }

    public function test_invalid_order_quantities()
    {
        $this->browse(function (Browser $browser) {
            // Login as packaging user
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in');

            // Test zero quantity
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '0')
                   ->press('Create Order')
                   ->assertSee('The quantity must be at least 1');

            // Test negative quantity
            $browser->type('quantity', '-1')
                   ->press('Create Order')
                   ->assertSee('The quantity must be at least 1');

            // Test quantity exceeding stock
            $browser->type('quantity', '20')
                   ->press('Create Order')
                   ->assertSee('Insufficient stock');

            // Test non-numeric input
            $browser->type('quantity', 'abc')
                   ->press('Create Order')
                   ->assertSee('The quantity must be a number');
        });
    }

    public function test_invalid_status_transitions()
    {
        $this->browse(function (Browser $browser) {
            // Login as packaging user
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in');

            // Create order
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->press('Create Order')
                   ->waitForText('Order created successfully');

            // Try to skip 'packed' status
            $browser->press('Mark as Out')
                   ->assertSee('Invalid status transition')
                   ->assertDontSee('Status updated to out');

            // Try to update canceled order
            $browser->press('Cancel Order')
                   ->waitForText('Order canceled')
                   ->press('Mark as Packed')
                   ->assertSee('Cannot modify canceled order')
                   ->assertDontSee('Status updated to packed');
        });
    }

    public function test_concurrent_stock_updates()
    {
        $this->browse(function (Browser $browser) {
            // Login as packaging user
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in');

            // Create first order using 7 units
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '7')
                   ->press('Create Order')
                   ->waitForText('Order created successfully');

            // Try to create second order with 5 units (should fail as only 3 left)
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->press('Create Order')
                   ->assertSee('Insufficient stock');
        });
    }

    public function test_form_validation_errors()
    {
        $this->browse(function (Browser $browser) {
            // Login as packaging user
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in');

            // Test empty form submission
            $browser->visit('/orders/create')
                   ->press('Create Order')
                   ->assertSee('The product field is required')
                   ->assertSee('The quantity field is required');

            // Test missing quantity
            $browser->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->press('Create Order')
                   ->assertSee('The quantity field is required');

            // Test invalid product
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', 'Nonexistent Product')
                   ->type('quantity', '1')
                   ->press('Create Order')
                   ->assertSee('Please select a valid product');
        });
    }

    public function test_low_stock_warnings()
    {
        $this->browse(function (Browser $browser) {
            // Login as packaging user
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in');

            // Create order that brings stock below threshold
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '6') // Will leave 4 items (below threshold of 5)
                   ->press('Create Order')
                   ->waitForText('Order created successfully');

            // Process to OUT status
            $browser->press('Mark as Packed')
                   ->waitForText('Status updated to packed')
                   ->press('Mark as Out')
                   ->waitForText('Status updated to out');

            // Check for low stock warning
            $browser->visit('/products')
                   ->assertSee('Low Stock Warning')
                   ->assertSee('4 units remaining');
        });
    }
}
