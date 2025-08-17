<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleOrderPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_order_page_loads_successfully()
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create some products
        Product::factory()->count(2)->create();

        // Visit the create order page
        $response = $this->actingAs($user)
            ->get('/orders/create');

        $response->assertStatus(200);
        $response->assertSee('Create Order');
    }

    /** @test */
    public function create_order_page_contains_submit_button()
    {
        $user = User::factory()->create();
        Product::factory()->count(2)->create();

        $response = $this->actingAs($user)
            ->get('/orders/create');

        $response->assertStatus(200);
        $response->assertSee('Submit Order');
        $response->assertSee('id="submitBtn"', false);
    }

    /** @test */
    public function create_order_page_contains_search_elements()
    {
        $user = User::factory()->create();
        Product::factory()->count(2)->create();

        $response = $this->actingAs($user)
            ->get('/orders/create');

        $response->assertStatus(200);
        $response->assertSee('🔍 Search');
        $response->assertSee('id="searchBtn"', false);
        $response->assertSee('id="productSearch"', false);
    }

    /** @test */
    public function create_order_page_has_form_with_correct_id()
    {
        $user = User::factory()->create();
        Product::factory()->count(2)->create();

        $response = $this->actingAs($user)
            ->get('/orders/create');

        $response->assertStatus(200);
        $response->assertSee('id="orderForm"', false);
        $response->assertSee('action="' . route('orders.store') . '"', false);
    }

    /** @test */
    public function create_order_page_contains_javascript_functions()
    {
        $user = User::factory()->create();
        Product::factory()->count(2)->create();

        $response = $this->actingAs($user)
            ->get('/orders/create');

        $response->assertStatus(200);
        $response->assertSee('performSearch');
        $response->assertSee('updateProductCount');
        $response->assertSee('showNoResultsMessage');
    }
}
