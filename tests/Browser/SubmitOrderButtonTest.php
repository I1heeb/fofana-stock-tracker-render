<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SubmitOrderButtonTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $user;
    protected $products;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test products
        $this->products = Product::factory()->count(3)->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 29.99,
            'stock_quantity' => 15
        ]);
    }

    /** @test */
    public function submit_order_button_is_visible_and_clickable()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#submitBtn')
                ->assertSee('📋 Submit Order')
                ->assertAttribute('#submitBtn', 'type', 'button')
                ->assertAttribute('#submitBtn', 'id', 'submitBtn');
        });
    }

    /** @test */
    public function search_button_works_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#searchBtn')
                ->assertSee('🔍 Search')
                ->type('#productSearch', 'Test')
                ->click('#searchBtn')
                ->pause(1000) // Attendre que la recherche se termine
                ->assertSee('Test Product'); // Vérifier que le produit est trouvé
        });
    }

    /** @test */
    public function submit_order_button_shows_validation_message_when_no_products_selected()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#submitBtn')
                ->click('#submitBtn')
                ->waitForDialog()
                ->assertDialogOpened()
                ->acceptDialog(); // Accepter l'alert de validation
        });
    }

    /** @test */
    public function submit_order_button_works_with_selected_products()
    {
        $product = $this->products->first();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#submitBtn')
                ->waitFor('input[name="products[' . $product->id . '][quantity]"]')
                ->type('input[name="products[' . $product->id . '][quantity]"]', '2')
                ->click('#submitBtn')
                ->waitForDialog()
                ->assertDialogOpened()
                ->acceptDialog() // Accepter la confirmation
                ->waitForLocation('/orders') // Attendre la redirection
                ->assertPathIs('/orders')
                ->assertSee('Order created successfully'); // Message de succès
        });
    }

    /** @test */
    public function search_functionality_filters_products_correctly()
    {
        // Créer des produits avec des noms différents
        Product::factory()->create(['name' => 'Apple iPhone', 'sku' => 'APL-001']);
        Product::factory()->create(['name' => 'Samsung Galaxy', 'sku' => 'SAM-001']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#productSearch')
                ->type('#productSearch', 'Apple')
                ->pause(500) // Attendre la recherche en temps réel
                ->assertSee('Apple iPhone')
                ->assertDontSee('Samsung Galaxy')
                ->clear('#productSearch')
                ->type('#productSearch', 'Samsung')
                ->pause(500)
                ->assertSee('Samsung Galaxy')
                ->assertDontSee('Apple iPhone');
        });
    }

    /** @test */
    public function keyboard_shortcuts_work_correctly()
    {
        $product = $this->products->first();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('input[name="products[' . $product->id . '][quantity]"]')
                ->type('input[name="products[' . $product->id . '][quantity]"]', '1')
                ->keys('body', ['{ctrl}', '{enter}']) // Ctrl+Enter
                ->waitForDialog()
                ->assertDialogOpened()
                ->dismissDialog(); // Annuler pour ce test
        });
    }

    /** @test */
    public function submit_button_shows_loading_state()
    {
        $product = $this->products->first();

        $this->browse(function (Browser $browser) use ($product) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('input[name="products[' . $product->id . '][quantity]"]')
                ->type('input[name="products[' . $product->id . '][quantity]"]', '1')
                ->click('#submitBtn')
                ->waitForDialog()
                ->acceptDialog()
                ->pause(100) // Court délai pour voir l'état de chargement
                ->assertSee('⏳ Submitting...'); // Vérifier l'état de chargement
        });
    }

    /** @test */
    public function product_count_updates_correctly_during_search()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#productSearch')
                ->assertSee('product(s) available') // Compteur initial
                ->type('#productSearch', 'Test')
                ->pause(500)
                ->assertSee('product(s) found out of'); // Compteur filtré
        });
    }

    /** @test */
    public function no_results_message_appears_when_no_products_match_search()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                ->visit('/orders/create')
                ->waitFor('#productSearch')
                ->type('#productSearch', 'NonExistentProduct123')
                ->pause(1000) // Attendre que la recherche se termine
                ->assertSee('No products found')
                ->assertSee('Try a different search term');
        });
    }
}
