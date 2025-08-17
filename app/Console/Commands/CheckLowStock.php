<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Events\LowStockDetected;
use Illuminate\Console\Command;

class CheckLowStock extends Command
{
    protected $signature = 'stock:check-low';
    protected $description = 'Check for products with low stock and trigger notifications';

    public function handle()
    {
        $lowStockProducts = Product::where('stock_quantity', '<=', \DB::raw('low_stock_threshold'))
            ->get();

        $this->info("Found {$lowStockProducts->count()} products with low stock");

        foreach ($lowStockProducts as $product) {
            event(new LowStockDetected(
                $product,
                $product->stock_quantity,
                $product->low_stock_threshold
            ));

            $this->line("- {$product->name}: {$product->stock_quantity} (threshold: {$product->low_stock_threshold})");
        }

        return Command::SUCCESS;
    }
}