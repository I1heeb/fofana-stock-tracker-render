<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class OrderCreationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_create_order_with_live_feedback()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'stock_quantity' => 10,
            'price' => 25.00
        ]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->assertSee('Create Order')
                ->type("items[{$product->id}]", '3')
                ->waitForText('3 items')
                ->waitForText('$75.00')
                ->press('Create Order')
                ->waitForLocation('/orders')
                ->assertSee('Order created successfully');
        });

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'in_progress'
        ]);
    }

    public function test_insufficient_stock_shows_error()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create([
            'stock_quantity' => 2,
            'price' => 25.00
        ]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->type("items[{$product->id}]", '5')
                ->press('Create Order')
                ->waitForText('Insufficient stock');
        });
    }

    public function test_keyboard_navigation_works()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->keys("input[name='items[{$product->id}]']", ['{tab}'])
                ->assertFocused("input[name='items[{$product->id}]']");
        });
    }
}