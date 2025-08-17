<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Log;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $order = Order::first();
        $product = Product::first();

        if ($user) {
            Log::create([
                'user_id' => $user->id,
                'action' => 'dashboard_access',
                'description' => 'User accessed the dashboard',
                'message' => 'Dashboard loaded successfully',
                'type' => 'info',
            ]);

            if ($order) {
                Log::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'action' => 'order_created',
                    'description' => 'New order was created',
                    'message' => "Order #{$order->id} created successfully",
                    'type' => 'success',
                ]);
            }

            if ($product) {
                Log::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'action' => 'stock_updated',
                    'description' => 'Product stock was updated',
                    'message' => "Stock updated for {$product->name}",
                    'type' => 'info',
                    'quantity' => 10,
                ]);
            }
        }

        // Add more sample logs
        Log::factory(20)->create();
    }
}


