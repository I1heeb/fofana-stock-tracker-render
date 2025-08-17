<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderSubmitButtonTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $products;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create test products
        $this->products = Product::factory()->count(3)->create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'stock_quantity' => 10,
            'price' => 25.99
        ]);
    }

    /** @test */
    public function user_can_access_create_order_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('orders.create'));

        $response->assertStatus(200);
        $response->assertViewIs('orders.create');
        $response->assertSee('Create Order');
        $response->assertSee('Submit Order'); // Notre nouveau bouton
    }

    /** @test */
    public function create_order_page_contains_submit_button_with_correct_attributes()
    {
        $response = $this->actingAs($this->user)
            ->get(route('orders.create'));

        $response->assertStatus(200);
        
        // Vérifier que le bouton Submit Order existe avec les bons attributs
        $response->assertSee('id="submitBtn"', false);
        $response->assertSee('📋 Submit Order');
        $response->assertSee('Submit the order (same as pressing Enter)');
    }

    /** @test */
    public function create_order_page_contains_search_functionality()
    {
        $response = $this->actingAs($this->user)
            ->get(route('orders.create'));

        $response->assertStatus(200);
        
        // Vérifier que la barre de recherche existe
        $response->assertSee('id="productSearch"', false);
        $response->assertSee('id="searchBtn"', false);
        $response->assertSee('🔍 Search');
    }

    /** @test */
    public function user_can_create_order_with_products()
    {
        $product1 = $this->products->first();
        $product2 = $this->products->get(1);

        $orderData = [
            'products' => [
                $product1->id => ['quantity' => 2],
                $product2->id => ['quantity' => 1],
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), $orderData);

        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('success');

        // Vérifier que la commande a été créée
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending'
        ]);

        // Vérifier les items de la commande
        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertEquals(2, $order->orderItems->count());
        
        // Vérifier les quantités
        $this->assertEquals(2, $order->orderItems->where('product_id', $product1->id)->first()->quantity);
        $this->assertEquals(1, $order->orderItems->where('product_id', $product2->id)->first()->quantity);
    }

    /** @test */
    public function order_creation_fails_without_products()
    {
        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), ['products' => []]);

        $response->assertSessionHasErrors();
        
        // Vérifier qu'aucune commande n'a été créée
        $this->assertDatabaseMissing('orders', [
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function order_creation_updates_product_stock()
    {
        $product = $this->products->first();
        $initialStock = $product->stock_quantity;
        $orderQuantity = 3;

        $orderData = [
            'products' => [
                $product->id => ['quantity' => $orderQuantity]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->post(route('orders.store'), $orderData);

        $response->assertRedirect(route('orders.index'));

        // Vérifier que le stock a été mis à jour
        $product->refresh();
        $this->assertEquals($initialStock - $orderQuantity, $product->stock_quantity);
    }

    /** @test */
    public function create_order_page_shows_product_information()
    {
        $product = $this->products->first();

        $response = $this->actingAs($this->user)
            ->get(route('orders.create'));

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->sku);
        $response->assertSee('$' . number_format($product->price, 2));
        $response->assertSee($product->stock_quantity . ' in stock');
    }

    /** @test */
    public function create_order_page_has_correct_javascript_elements()
    {
        $response = $this->actingAs($this->user)
            ->get(route('orders.create'));

        $response->assertStatus(200);
        
        // Vérifier que les éléments JavaScript nécessaires sont présents
        $response->assertSee('document.getElementById(\'productSearch\')');
        $response->assertSee('document.getElementById(\'searchBtn\')');
        $response->assertSee('document.getElementById(\'submitBtn\')');
        $response->assertSee('document.getElementById(\'orderForm\')');
        $response->assertSee('performSearch');
    }
}
