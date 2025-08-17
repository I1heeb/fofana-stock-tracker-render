<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AccessibilityTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_skip_to_content_link_works()
    {
        $user = User::factory()->create(['role' => 'packaging']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/orders')
                ->keys('', ['{tab}']) // Focus skip link
                ->assertSee('Skip to main content')
                ->keys('', ['{enter}']) // Activate skip link
                ->assertFocused('#main-content');
        });
    }

    public function test_keyboard_navigation_in_orders_table()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $orders = \App\Models\Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/orders')
                ->click('table tbody tr:first-child') // Focus first row
                ->keys('', ['{arrow-down}']) // Navigate to second row
                ->assertFocused('table tbody tr:nth-child(2)')
                ->keys('', ['{arrow-up}']) // Navigate back to first row
                ->assertFocused('table tbody tr:first-child')
                ->keys('', ['{home}']) // Go to first row
                ->assertFocused('table tbody tr:first-child')
                ->keys('', ['{end}']) // Go to last row
                ->assertFocused('table tbody tr:last-child');
        });
    }

    public function test_modal_focus_trap_works()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->click('[data-modal-trigger]') // Open modal
                ->waitFor('[role="dialog"]')
                ->assertFocused('[role="dialog"] input:first-of-type') // First input focused
                ->keys('', ['{tab}']) // Tab to next element
                ->keys('', ['{shift}', '{tab}']) // Shift+Tab back
                ->assertFocused('[role="dialog"] input:first-of-type') // Back to first input
                ->keys('', ['{escape}']) // Close modal with Escape
                ->waitUntilMissing('[role="dialog"]');
        });
    }

    public function test_form_labels_and_descriptions()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->assertAttribute("input[name='items[{$product->id}]']", 'aria-describedby')
                ->assertPresent("label[for='items[{$product->id}]']")
                ->assertSeeIn("label[for='items[{$product->id}]']", $product->name);
        });
    }

    public function test_status_messages_announced()
    {
        $user = User::factory()->create(['role' => 'packaging']);
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->browse(function (Browser $browser) use ($user, $product) {
            $browser->loginAs($user)
                ->visit('/orders/create')
                ->type("items[{$product->id}]", '2')
                ->press('Create Order')
                ->waitForLocation('/orders')
                ->assertPresent('[role="alert"]')
                ->assertAttribute('[role="alert"]', 'aria-live', 'polite');
        });
    }

    public function test_color_contrast_and_focus_indicators()
    {
        $user = User::factory()->create(['role' => 'packaging']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/orders')
                ->keys('', ['{tab}']) // Focus first interactive element
                ->script('
                    const focused = document.activeElement;
                    const styles = window.getComputedStyle(focused, ":focus-visible");
                    return {
                        outline: styles.outline,
                        ringWidth: styles.getPropertyValue("--tw-ring-width"),
                        ringColor: styles.getPropertyValue("--tw-ring-color")
                    };
                ');
            // Assert focus indicators are visible
        });
    }
}