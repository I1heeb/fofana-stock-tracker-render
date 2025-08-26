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

        // Add more sample logs manually (no Faker required)
        $sampleLogs = [
            ['action' => 'user_login', 'description' => 'User logged in', 'message' => 'Login successful', 'type' => 'info'],
            ['action' => 'product_view', 'description' => 'Product viewed', 'message' => 'Product details accessed', 'type' => 'info'],
            ['action' => 'order_update', 'description' => 'Order status updated', 'message' => 'Order status changed', 'type' => 'success'],
            ['action' => 'stock_check', 'description' => 'Stock level checked', 'message' => 'Inventory reviewed', 'type' => 'info'],
            ['action' => 'user_logout', 'description' => 'User logged out', 'message' => 'Session ended', 'type' => 'info'],
        ];

        foreach ($sampleLogs as $logData) {
            Log::create([
                'user_id' => $user ? $user->id : null,
                'action' => $logData['action'],
                'description' => $logData['description'],
                'message' => $logData['message'],
                'type' => $logData['type'],
                'created_at' => now()->subDays(rand(1, 10)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);
        }
    }
}


