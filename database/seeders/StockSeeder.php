<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // Create test products with SKU
        $products = [
            [
                'name' => 'Product 1', 
                'sku' => 'PROD-001',
                'price' => 10.99, 
                'stock_quantity' => 100
            ],
            [
                'name' => 'Product 2', 
                'sku' => 'PROD-002',
                'price' => 25.50, 
                'stock_quantity' => 50
            ],
            [
                'name' => 'Product 3', 
                'sku' => 'PROD-003',
                'price' => 5.99, 
                'stock_quantity' => 200
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(
                ['sku' => $productData['sku']], 
                $productData
            );
        }

        // Create test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create test order with valid status
        $order = Order::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            [
                'order_number' => 'ORD-' . rand(1000, 9999),
                'total_amount' => 36.49,
            ]
        );

        // Create order items if not exist
        $product1 = Product::where('sku', 'PROD-001')->first();
        $product2 = Product::where('sku', 'PROD-002')->first();

        if ($product1) {
            OrderItem::firstOrCreate([
                'order_id' => $order->id,
                'product_id' => $product1->id,
                'quantity' => 2,
                'price' => 10.99,
            ]);
        }

        if ($product2) {
            OrderItem::firstOrCreate([
                'order_id' => $order->id,
                'product_id' => $product2->id,
                'quantity' => 1,
                'price' => 25.50,
            ]);
        }
    }
}



