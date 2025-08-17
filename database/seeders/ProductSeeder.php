<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Dell XPS 13',
                'description' => 'High-performance ultrabook with Intel Core i7',
                'price' => 1299.99,
                'stock_quantity' => 25,
                'minimum_stock' => 5,
                'low_stock_threshold' => 10,
                'sku' => 'DELL-XPS13-001',
            ],
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with A17 Pro chip',
                'price' => 999.99,
                'stock_quantity' => 50,
                'minimum_stock' => 10,
                'low_stock_threshold' => 15,
                'sku' => 'IPHONE-15PRO-001',
            ],
            [
                'name' => 'Samsung Galaxy S24',
                'description' => 'Android flagship smartphone',
                'price' => 799.99,
                'stock_quantity' => 3,
                'minimum_stock' => 5,
                'low_stock_threshold' => 8,
                'sku' => 'SAMSUNG-S24-001',
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'Apple laptop with M3 chip',
                'price' => 1199.99,
                'stock_quantity' => 15,
                'minimum_stock' => 3,
                'low_stock_threshold' => 5,
                'sku' => 'MBA-M3-001',
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Noise-canceling wireless headphones',
                'price' => 399.99,
                'stock_quantity' => 2,
                'minimum_stock' => 5,
                'low_stock_threshold' => 10,
                'sku' => 'SONY-WH1000XM5-001',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
