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

        Order::factory(10)->create()->each(function ($order) use ($products) {
            // Create 1-3 order items per order
            OrderItem::factory(rand(1, 3))->create([
                'order_id' => $order->id,
                'product_id' => $products->random()->id,
            ]);
        });
    }
}


