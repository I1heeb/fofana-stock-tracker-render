<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductBasicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product can be created with required fields
     */
    public function test_product_can_be_created_with_required_fields(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'stock_quantity' => 50,
            'sku' => 'TEST-001',
            'supplier_id' => $supplier->id
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'price' => 99.99,
            'stock_quantity' => 50
        ]);
    }

    /**
     * Test product default values
     */
    public function test_product_has_correct_default_values(): void
    {
        $product = new Product();
        
        $this->assertEquals(10, $product->low_stock_threshold);
        $this->assertEquals(0, $product->stock_quantity);
    }

    /**
     * Test product price casting
     */
    public function test_product_price_is_cast_correctly(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => '99.99',
            'sku' => 'TEST-002',
            'supplier_id' => $supplier->id
        ]);
        
        // Le prix est casté en decimal, donc c'est une string
        $this->assertEquals('99.99', $product->price);
        $this->assertIsNumeric($product->price);
    }

    /**
     * Test low stock detection logic
     */
    public function test_low_stock_detection_logic(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $lowStockProduct = Product::create([
            'name' => 'Low Stock Product',
            'price' => 10.00,
            'stock_quantity' => 5,
            'low_stock_threshold' => 10,
            'sku' => 'LOW-001',
            'supplier_id' => $supplier->id
        ]);

        $normalStockProduct = Product::create([
            'name' => 'Normal Stock Product',
            'price' => 20.00,
            'stock_quantity' => 20,
            'low_stock_threshold' => 10,
            'sku' => 'NORMAL-001',
            'supplier_id' => $supplier->id
        ]);

        // Test low stock condition
        $this->assertTrue($lowStockProduct->stock_quantity <= $lowStockProduct->low_stock_threshold);
        $this->assertFalse($normalStockProduct->stock_quantity <= $normalStockProduct->low_stock_threshold);
    }

    /**
     * Test product relationships are defined
     */
    public function test_product_relationships_are_defined(): void
    {
        $supplier = Supplier::create([
            'name' => 'Test Supplier',
            'email' => 'supplier@example.com',
            'phone' => '123456789'
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 15.00,
            'sku' => 'TEST-003',
            'supplier_id' => $supplier->id
        ]);

        // Test relationships exist
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->supplier());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->orderItems());
    }
}
