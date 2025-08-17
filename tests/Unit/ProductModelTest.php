<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product creation with required fields
     */
    public function test_product_can_be_created_with_required_fields(): void
    {
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 50,
            'sku' => 'TEST-001'
        ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 50,
            'sku' => 'TEST-001'
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
    public function test_product_price_is_cast_to_decimal(): void
    {
        $product = Product::factory()->create(['price' => '99.99']);

        $this->assertIsFloat($product->price);
        $this->assertEquals(99.99, $product->price);
    }

    /**
     * Test low stock detection
     */
    public function test_product_low_stock_detection(): void
    {
        $lowStockProduct = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10
        ]);

        $normalStockProduct = Product::factory()->create([
            'stock_quantity' => 20,
            'low_stock_threshold' => 10
        ]);

        $this->assertTrue($lowStockProduct->stock_quantity <= $lowStockProduct->low_stock_threshold);
        $this->assertFalse($normalStockProduct->stock_quantity <= $normalStockProduct->low_stock_threshold);
    }

    /**
     * Test product supplier relationship
     */
    public function test_product_belongs_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $product->supplier);
        $this->assertEquals($supplier->id, $product->supplier->id);
    }

    /**
     * Test product order items relationship
     */
    public function test_product_has_many_order_items(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->orderItems());
    }

    /**
     * Test product purchase order items relationship
     */
    public function test_product_has_many_purchase_order_items(): void
    {
        $product = Product::factory()->create();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $product->purchaseOrderItems());
    }

    /**
     * Test SKU uniqueness
     */
    public function test_product_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'UNIQUE-001']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Product::factory()->create(['sku' => 'UNIQUE-001']);
    }
}
