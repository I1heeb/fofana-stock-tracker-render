<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Chrome;

class OrderFlowTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $packagingUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a packaging user
        $this->packagingUser = User::factory()->create([
            'role' => 'packaging',
            'email' => 'packer@fofana.test',
            'password' => bcrypt('secret'),
        ]);

        // Create a test product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'stock_quantity' => 20,
            'low_stock_threshold' => 5,
            'sku' => 'TEST-001',
        ]);
    }

    public function test_full_order_lifecycle_and_stock_restoration()
    {
        $this->browse(function (Browser $browser) {
            // 1. Login
            $browser->visit('/login')
                   ->type('email', $this->packagingUser->email)
                   ->type('password', 'secret')
                   ->press('Log in')
                   ->assertPathIs('/dashboard');

            // 2. Create new order
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->press('Create Order')
                   ->waitForText('Order created successfully')
                   ->assertSee($this->product->sku);

            // Get the order ID from the URL
            $orderId = $browser->driver->getCurrentURL();
            preg_match('/orders\/(\d+)/', $orderId, $matches);
            $orderId = $matches[1];

            // 3. Update status to Packed
            $browser->visit("/orders/{$orderId}")
                   ->press('Mark as Packed')
                   ->waitForText('Status updated to packed')
                   ->assertSee('packed');

            // 4. Update status to Out
            $browser->press('Mark as Out')
                   ->waitForText('Status updated to out')
                   ->assertSee('out');

            // 5. Verify stock reduction
            $browser->visit('/products')
                   ->assertSee($this->product->name)
                   ->assertSee('15'); // Initial 20 - 5 ordered

            // 6. Cancel order
            $browser->visit("/orders/{$orderId}")
                   ->press('Cancel Order')
                   ->waitForText('Order canceled')
                   ->assertSee('canceled');

            // 7. Verify stock restoration
            $browser->visit('/products')
                   ->assertSee($this->product->name)
                   ->assertSee('20'); // Stock restored

            // 8. Create another order for return testing
            $browser->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '3')
                   ->press('Create Order')
                   ->waitForText('Order created successfully');

            // Get the new order ID
            $newOrderId = $browser->driver->getCurrentURL();
            preg_match('/orders\/(\d+)/', $newOrderId, $matches);
            $newOrderId = $matches[1];

            // 9. Process to Out status
            $browser->visit("/orders/{$newOrderId}")
                   ->press('Mark as Packed')
                   ->waitForText('Status updated to packed')
                   ->press('Mark as Out')
                   ->waitForText('Status updated to out');

            // 10. Process return
            $browser->press('Return Order')
                   ->waitForText('Order returned')
                   ->assertSee('returned');

            // 11. Check logs
            $browser->visit('/logs')
                   ->assertSee('return')
                   ->assertSee($this->packagingUser->name)
                   ->assertSee($this->product->sku);
        });
    }
}
