<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SimpleSeeder extends Seeder
{
    public function run(): void
    {
        // Create a user first
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => User::ROLE_PACKAGING_AGENT,
        ]);

        // Create some products
        Product::create([
            'name' => 'Sample Product 1',
            'description' => 'A sample product',
            'price' => 19.99,
            'stock_quantity' => 100,
            'minimum_stock' => 10,
            'low_stock_threshold' => 20,
            'sku' => 'PROD-001',
        ]);

        Product::create([
            'name' => 'Sample Product 2',
            'description' => 'Another sample product',
            'price' => 29.99,
            'stock_quantity' => 5,
            'minimum_stock' => 10,
            'low_stock_threshold' => 20,
            'sku' => 'PROD-002',
        ]);
    }
}