<?php

namespace App\Observers;

 use App\Services\CacheService;
use App\Models\Order;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class OrderObserver
{
    public function created(Order $order): void
    {
        // Only deduct stock if order is created with 'processing' status
        if ($order->status === 'processing') {
            $this->handleStockAdjustment($order, null, 'processing');
        }
        
        CacheService::clearDashboardCache();
    }

public function updated(Order $order): void
{
    $originalStatus = $order->getOriginal('status');
    $newStatus = $order->status;

    // Only act when status actually changes
    if ($originalStatus === $newStatus) {
        return;
    }

    // Vider le cache
    CacheService::clearDashboardCache();

    // Gérer les changements de stock
    $this->handleStockAdjustment($order, $originalStatus, $newStatus);
}

private function handleStockAdjustment(Order $order, ?string $oldStatus, string $newStatus): void
{
    DB::transaction(function () use ($order, $oldStatus, $newStatus) {
        foreach ($order->orderItems as $item) {
            $product = $item->product;
            $quantity = $item->quantity;

            // RÉDUIRE le stock quand commande sort (out)
            if ($newStatus === 'out' && ($oldStatus === null || $oldStatus !== 'out')) {
                $oldStock = $product->stock_quantity;
                
                DB::table('products')
                    ->where('id', $product->id)
                    ->decrement('stock_quantity', $quantity);
                
                Log::create([
                    'user_id' => Auth::id() ?? $order->user_id,
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'action' => 'stock_reduced',
                    'description' => 'Stock reduced due to order shipment',
                    'message' => "Stock for {$product->name} reduced from {$oldStock} to " . ($oldStock - $quantity),
                    'type' => 'stock',
                    'quantity' => -$quantity,
                ]);
            }

            // RESTAURER le stock si annulé/retourné
            if (in_array($newStatus, ['cancelled', 'returned']) &&
                ($oldStatus === null || !in_array($oldStatus, ['cancelled', 'returned']))) {
                $oldStock = $product->stock_quantity;
                
                DB::table('products')
                    ->where('id', $product->id)
                    ->increment('stock_quantity', $quantity);
                
                Log::create([
                    'user_id' => Auth::id() ?? $order->user_id,
                    'product_id' => $product->id,
                    'order_id' => $order->id,
                    'action' => 'stock_restored',
                    'description' => "Stock restored due to order {$newStatus}",
                    'message' => "Stock for {$product->name} restored from {$oldStock} to " . ($oldStock + $quantity),
                    'type' => 'stock',
                    'quantity' => $quantity,
                ]);
            }
        }
    });
}
}


