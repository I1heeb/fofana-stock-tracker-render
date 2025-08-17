<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Log;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_low_stock_returns_true_when_below_threshold()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 5,
            'low_stock_threshold' => 10
        ]);

        $this->assertTrue($product->isLowStock());
    }

    public function test_is_low_stock_returns_false_when_above_threshold()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 15,
            'low_stock_threshold' => 10
        ]);

        $this->assertFalse($product->isLowStock());
    }

    public function test_product_has_order_items_relationship()
    {
        $product = Product::factory()->create();
        OrderItem::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $product->orderItems);
        $this->assertCount(1, $product->orderItems);
    }

    public function test_product_has_logs_relationship()
    {
        $product = Product::factory()->create();
        Log::factory()->create(['product_id' => $product->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $product->logs);
        $this->assertCount(1, $product->logs);
    }

    public function test_product_belongs_to_supplier()
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['supplier_id' => $supplier->id]);

        $this->assertInstanceOf(Supplier::class, $product->supplier);
        $this->assertEquals($supplier->id, $product->supplier->id);
    }

    public function test_price_is_cast_to_decimal()
    {
        $product = Product::factory()->create(['price' => 19.99]);

        $this->assertIsString($product->price);
        $this->assertEquals('19.99', $product->price);
    }
}