<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::all();

        // Skip order creation if no products exist (ProductSeeder should have created them)
        if ($products->isEmpty()) {
            $this->command->info('No products found, skipping order creation');
            return;
        }

        // Create sample orders manually (no Faker required)
        $sampleOrders = [
            ['order_number' => 'ORD-2025-001', 'status' => 'pending', 'total_amount' => 150.00],
            ['order_number' => 'ORD-2025-002', 'status' => 'processing', 'total_amount' => 89.50],
            ['order_number' => 'ORD-2025-003', 'status' => 'packed', 'total_amount' => 245.75],
            ['order_number' => 'ORD-2025-004', 'status' => 'completed', 'total_amount' => 67.25],
            ['order_number' => 'ORD-2025-005', 'status' => 'pending', 'total_amount' => 198.00],
        ];

        foreach ($sampleOrders as $orderData) {
            $order = Order::create([
                'user_id' => $users->random()->id,
                'order_number' => $orderData['order_number'],
                'status' => $orderData['status'],
                'total_amount' => $orderData['total_amount'],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);

            // Create 1-3 order items per order
            $itemCount = rand(1, 3);
            for ($i = 0; $i < $itemCount; $i++) {
                $product = $products->random();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => rand(1, 5),
                    'price' => $product->price ?? 25.00,
                ]);
            }
        }
    }
}


