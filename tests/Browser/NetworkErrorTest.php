<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class NetworkErrorTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected User $packagingUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->packagingUser = User::factory()->create([
            'role' => 'packaging',
            'email' => 'packer@fofana.test',
            'password' => bcrypt('secret'),
        ]);

        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'stock_quantity' => 20,
            'sku' => 'TEST-001',
        ]);
    }

    /**
     * Simulate completely offline and assert the AJAX call fails gracefully.
     */
    public function test_offline_add_to_cart_fails()
    {
        $this->browse(function (Browser $browser) {
            // Enable DevTools Network domain and go offline
            $browser->driver->executeCdpCommand('Network.enable', []);
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => true,
                'latency' => 0,
                'downloadThroughput' => 0,
                'uploadThroughput' => 0,
            ]);

            // Perform the action
            $browser->loginAs($this->packagingUser)
                   ->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->click('@add-to-cart-button')
                   ->pause(500) // wait for JS
                   ->assertSee('Unable to reach server')
                   ->assertVisible('@network-error-alert')
                   ->assertSee('Please check your internet connection');

            // Restore online for subsequent tests
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => false,
                'latency' => 20,
                'downloadThroughput' => 5 * 1024 * 1024,
                'uploadThroughput' => 5 * 1024 * 1024,
            ]);
        });
    }

    /**
     * Simulate high latency and assert the loader appears.
     */
    public function test_slow_connection_shows_loader()
    {
        $this->browse(function (Browser $browser) {
            // Throttle network
            $browser->driver->executeCdpCommand('Network.enable', []);
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => false,
                'latency' => 1000,           // 1s latency
                'downloadThroughput' => 50 * 1024,      // 50 KB/s
                'uploadThroughput' => 50 * 1024,      // 50 KB/s
            ]);

            // Trigger the AJAX
            $browser->loginAs($this->packagingUser)
                   ->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->click('@add-to-cart-button')
                   ->pause(200)                    // loader should already be visible
                   ->assertVisible('@ajax-loader')
                   ->pause(1200)                   // wait for request to finish
                   ->assertNotVisible('@ajax-loader')
                   ->assertSee('Item added to order');
        });
    }

    /**
     * Test timeout handling for long-running requests.
     */
    public function test_request_timeout_handling()
    {
        $this->browse(function (Browser $browser) {
            // Simulate very slow connection
            $browser->driver->executeCdpCommand('Network.enable', []);
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => false,
                'latency' => 31000,          // 31s latency (exceeds typical 30s timeout)
                'downloadThroughput' => 1024, // 1 KB/s
                'uploadThroughput' => 1024,  // 1 KB/s
            ]);

            // Attempt the action
            $browser->loginAs($this->packagingUser)
                   ->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5')
                   ->click('@add-to-cart-button')
                   ->pause(200)
                   ->assertVisible('@ajax-loader')
                   ->pause(31000)
                   ->assertSee('Request timed out')
                   ->assertVisible('@timeout-error-alert');

            // Restore normal network conditions
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => false,
                'latency' => 20,
                'downloadThroughput' => 5 * 1024 * 1024,
                'uploadThroughput' => 5 * 1024 * 1024,
            ]);
        });
    }

    /**
     * Test intermittent connection handling.
     */
    public function test_intermittent_connection_handling()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->packagingUser)
                   ->visit('/orders/create')
                   ->waitFor('.product-search')
                   ->type('.product-search', $this->product->name)
                   ->waitFor('.product-item')
                   ->click('.product-item')
                   ->type('quantity', '5');

            // Go offline just before submitting
            $browser->driver->executeCdpCommand('Network.enable', []);
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => true,
                'latency' => 0,
                'downloadThroughput' => 0,
                'uploadThroughput' => 0,
            ]);

            $browser->click('@add-to-cart-button')
                   ->pause(500)
                   ->assertSee('Unable to reach server');

            // Come back online and retry
            $browser->driver->executeCdpCommand('Network.emulateNetworkConditions', [
                'offline' => false,
                'latency' => 20,
                'downloadThroughput' => 5 * 1024 * 1024,
                'uploadThroughput' => 5 * 1024 * 1024,
            ]);

            $browser->click('@retry-button')
                   ->pause(500)
                   ->assertSee('Item added to order');
        });
    }
} 