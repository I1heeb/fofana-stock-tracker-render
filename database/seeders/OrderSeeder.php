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

        if ($products->isEmpty()) {
            Product::factory(20)->create();
            $products = Product::all();
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


