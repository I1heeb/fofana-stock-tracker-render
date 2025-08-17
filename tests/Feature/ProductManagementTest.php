<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin can view products page
     */
    public function test_admin_can_view_products_page(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($admin)->get('/products');
        
        $response->assertStatus(200);
    }

    /**
     * Test admin can create product
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'permissions' => ['manage_products', 'manage_users', 'view_reports']
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

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
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001'
        ]);
    }

    /**
     * Test product search functionality
     */
    public function test_product_search_functionality(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $product1 = Product::create([
            'name' => 'iPhone 15',
            'price' => 999.99,
            'sku' => 'IPHONE-001',
            'supplier_id' => $supplier->id
        ]);

        $product2 = Product::create([
            'name' => 'Samsung Galaxy',
            'price' => 799.99,
            'sku' => 'SAMSUNG-001',
            'supplier_id' => $supplier->id
        ]);

        // Search by name
        $response = $this->actingAs($admin)
            ->get('/products?search=iPhone');
        
        $response->assertStatus(200);
        $response->assertSee('iPhone 15');
        $response->assertDontSee('Samsung Galaxy');
    }

    /**
     * Test low stock products page
     */
    public function test_low_stock_products_page(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $lowStockProduct = Product::create([
            'name' => 'Low Stock Product',
            'price' => 50.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'sku' => 'LOW-001',
            'supplier_id' => $supplier->id
        ]);

        $normalStockProduct = Product::create([
            'name' => 'Normal Stock Product',
            'price' => 60.00,
            'stock_quantity' => 50,
            'low_stock_threshold' => 10,
            'sku' => 'NORMAL-001',
            'supplier_id' => $supplier->id
        ]);

        $response = $this->actingAs($admin)->get('/products/low-stock');
        
        $response->assertStatus(200);
        $response->assertSee('Low Stock Product');
        $response->assertDontSee('Normal Stock Product');
    }
}
