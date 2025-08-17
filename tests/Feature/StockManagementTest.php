<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can create product
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $supplier = Supplier::factory()->create();

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 100,
            'low_stock_threshold' => 10,
            'minimum_stock' => 5,
            'sku' => 'TEST-001',
            'supplier_id' => $supplier->id
        ];

        $response = $this->actingAs($admin)
            ->post('/products', $productData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99
        ]);
    }

    /**
     * Test stock adjustment functionality
     */
    public function test_stock_adjustment_functionality(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $product = Product::factory()->create([
            'stock_quantity' => 50
        ]);

        // Test stock increase
        $response = $this->actingAs($admin)
            ->patch("/products/{$product->id}/adjust-stock", [
                'adjustment_type' => 'increase',
                'quantity' => 20,
                'reason' => 'Stock replenishment'
            ]);

        $response->assertRedirect();
        $this->assertEquals(70, $product->fresh()->stock_quantity);

        // Test stock decrease
        $response = $this->actingAs($admin)
            ->patch("/products/{$product->id}/adjust-stock", [
                'adjustment_type' => 'decrease',
                'quantity' => 10,
                'reason' => 'Damaged goods'
            ]);

        $response->assertRedirect();
        $this->assertEquals(60, $product->fresh()->stock_quantity);
    }

    /**
     * Test low stock detection
     */
    public function test_low_stock_detection(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        // Create products with different stock levels
        $lowStockProduct = Product::factory()->create([
            'name' => 'Low Stock Product',
            'stock_quantity' => 5,
            'low_stock_threshold' => 10
        ]);

        $normalStockProduct = Product::factory()->create([
            'name' => 'Normal Stock Product',
            'stock_quantity' => 50,
            'low_stock_threshold' => 10
        ]);

        $response = $this->actingAs($admin)->get('/products/low-stock');

        $response->assertStatus(200);
        $response->assertSee('Low Stock Product');
        $response->assertDontSee('Normal Stock Product');
    }

    /**
     * Test product search functionality
     */
    public function test_product_search_functionality(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'permissions' => User::getDefaultPermissions(User::ROLE_ADMIN)
        ]);

        $product1 = Product::factory()->create(['name' => 'iPhone 15']);
        $product2 = Product::factory()->create(['name' => 'Samsung Galaxy']);
        $product3 = Product::factory()->create(['sku' => 'IPHONE-001']);

        // Search by name
        $response = $this->actingAs($admin)
            ->get('/products?search=iPhone');

        $response->assertStatus(200);
        $response->assertSee('iPhone 15');
        $response->assertDontSee('Samsung Galaxy');

        // Search by SKU
        $response = $this->actingAs($admin)
            ->get('/products?search=IPHONE-001');

        $response->assertStatus(200);
        $response->assertSee($product3->name);
    }
}
