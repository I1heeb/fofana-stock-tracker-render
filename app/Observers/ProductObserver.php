<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Log;
use App\Events\LowStockDetected;

class ProductObserver
{
    public function updated(Product $product): void
{
    // Vérifier si le stock a changé et est maintenant bas
    if ($product->wasChanged('stock_quantity')) {
        $oldStock = $product->getOriginal('stock_quantity');
        $newStock = $product->stock_quantity;
        
        // Log du changement de stock
        Log::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'action' => 'stock_updated',
            'description' => 'Product stock was updated',
            'message' => "Stock updated for {$product->name}: {$oldStock} → {$newStock}",
            'type' => 'stock',
            'quantity' => $newStock - $oldStock,
            'created_at' => now(),
        ]);

        // Notification de stock bas (désactivée temporairement)
        // if ($product->isLowStock()) {
        //     event(new LowStockDetected($product, $newStock, $product->low_stock_threshold));
        // }
    }
}
}