<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ForceStockTest extends Command
{
    protected $signature = 'force:stock-test';
    protected $description = 'Force test stock update';

    public function handle()
    {
        // Créer données de test
        $user = User::first() ?? User::factory()->create();
        $product = Product::factory()->create(['stock_quantity' => 100]);
        
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'TEST-' . rand(1000, 9999),
            'status' => 'packed',
            'total_amount' => 50.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'price' => 5.00,
        ]);

        $this->info("AVANT - Product Stock: {$product->stock_quantity}");
        $this->info("Order Status: {$order->status}");

        // SEULEMENT changer le statut - laisser l'Observer faire le travail
        $order->update(['status' => 'out']);

        // Vérifier
        $product->refresh();
        $this->info("APRÈS - Product Stock: {$product->stock_quantity}");
        $this->info("Order Status: {$order->status}");

        if ($product->stock_quantity === 90) {
            $this->info("✅ SUCCESS: Stock updated correctly!");
        } else {
            $this->warn("⚠️  Stock = {$product->stock_quantity} (expected 90)");
            $this->info("Stock was reduced by: " . (100 - $product->stock_quantity));
        }

        return Command::SUCCESS;
    }
}