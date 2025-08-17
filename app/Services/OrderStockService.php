<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Log;
use Illuminate\Support\Facades\DB;

class OrderStockService
{
    public function updateOrderStatus(Order $order, string $newStatus): void
    {
        $oldStatus = $order->status;

        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            // MISE À JOUR DU STOCK AVANT changement de statut
            $this->handleStockChange($order, $oldStatus, $newStatus);
            
            // Changer le statut
            $order->update(['status' => $newStatus]);
        });
    }

    private function handleStockChange(Order $order, string $oldStatus, string $newStatus): void
{
    // Charger les items avec les produits
    $order->load('orderItems.product');

    foreach ($order->orderItems as $item) {
        $product = $item->product;
        $quantity = $item->quantity;

        // RÉDUIRE le stock quand commande sort
        if ($newStatus === Order::STATUS_OUT && $oldStatus !== Order::STATUS_OUT) {
            $oldStock = $product->stock_quantity;
            
            // MISE À JOUR DIRECTE
            DB::table('products')
                ->where('id', $product->id)
                ->decrement('stock_quantity', $quantity);
            
            // Recharger le produit
            $product->refresh();
            
            // Log
            Log::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'order_id' => $order->id,
                'action' => 'stock_reduced',
                'description' => 'Stock reduced due to order shipment',
                'message' => "Stock for {$product->name} reduced from {$oldStock} to {$product->stock_quantity}",
                'type' => 'stock',
                'quantity' => -$quantity,
            ]);
        }

        // RESTAURER le stock si annulé/retourné
        if (in_array($newStatus, [Order::STATUS_CANCELLED, Order::STATUS_RETURNED]) && 
            !in_array($oldStatus, [Order::STATUS_CANCELLED, Order::STATUS_RETURNED])) {
            $oldStock = $product->stock_quantity;
            
            // MISE À JOUR DIRECTE
            DB::table('products')
                ->where('id', $product->id)
                ->increment('stock_quantity', $quantity);
            
            // Recharger le produit
            $product->refresh();
            
            // Log
            Log::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'order_id' => $order->id,
                'action' => 'stock_restored',
                'description' => "Stock restored due to order {$newStatus}",
                'message' => "Stock for {$product->name} restored from {$oldStock} to {$product->stock_quantity}",
                'type' => 'stock',
                'quantity' => $quantity,
            ]);
        }
    }
}
}