<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Log;
use App\Events\LowStockDetected;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function handleOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        DB::transaction(function () use ($order, $oldStatus, $newStatus) {
            // Réduire le stock quand la commande passe à "out"
            if ($newStatus === 'out' && $oldStatus !== 'out') {
                foreach ($order->orderItems as $item) {
                    $this->reduceStock($item->product, $item->quantity, $order);
                }
            }

            // Restaurer le stock si commande annulée ou retournée
            if (in_array($newStatus, ['cancelled', 'returned']) && !in_array($oldStatus, ['cancelled', 'returned'])) {
                foreach ($order->orderItems as $item) {
                    $this->restoreStock($item->product, $item->quantity, $order, $newStatus);
                }
            }

            // PRD Compliance: Deduct stock when moving TO "In Progress"
            if ($newStatus === 'in_progress' && $oldStatus !== 'in_progress') {
                foreach ($order->orderItems as $item) {
                    $this->adjustStock(
                        product: $item->product,
                        change: -$item->quantity,
                        type: 'order_in_progress',
                        notes: "Stock deducted - Order marked In Progress"
                    );
                }
            }
        });
    }

    private function reduceStock(Product $product, int $quantity, Order $order): void
    {
        $oldStock = $product->stock_quantity;
        $product->stock_quantity -= $quantity;
        $product->save();

        // Log automatique
        Log::create([
            'user_id' => Auth::id() ?? $order->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'action' => 'stock_reduced',
            'description' => "Stock reduced due to order shipment",
            'message' => "Stock for {$product->name} reduced from {$oldStock} to {$product->stock_quantity} (Order #{$order->order_number})",
            'type' => 'stock',
            'quantity' => -$quantity,
            'created_at' => now(),
        ]);

        // Vérification automatique du stock faible
        if ($product->isLowStock()) {
            event(new LowStockDetected(
                $product,
                $product->stock_quantity,
                $product->low_stock_threshold
            ));
        }
    }

    private function restoreStock(Product $product, int $quantity, Order $order, string $reason): void
    {
        $oldStock = $product->stock_quantity;
        $product->stock_quantity += $quantity;
        $product->save();

        Log::create([
            'user_id' => Auth::id() ?? $order->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'action' => 'stock_restored',
            'description' => "Stock restored due to order {$reason}",
            'message' => "Stock for {$product->name} restored from {$oldStock} to {$product->stock_quantity} (Order #{$order->order_number})",
            'type' => 'stock',
            'quantity' => $quantity,
            'created_at' => now(),
        ]);
    }

    public function autoRestock(Product $product): bool
    {
        if (!$product->supplier || !$product->isLowStock()) {
            return false;
        }

        // Calculer la quantité recommandée
        $recommendedQuantity = max(
            $product->minimum_stock * 2,
            $product->low_stock_threshold * 3
        );

        // Créer une commande d'achat automatique (si vous avez ce système)
        // PurchaseOrder::create([...]);

        Log::create([
            'user_id' => 1, // System user
            'product_id' => $product->id,
            'action' => 'auto_restock_triggered',
            'description' => 'Automatic restock triggered for low stock product',
            'message' => "Auto-restock triggered for {$product->name}. Recommended quantity: {$recommendedQuantity}",
            'type' => 'system',
            'quantity' => $recommendedQuantity,
            'created_at' => now(),
        ]);

        return true;
    }

    public function startPackaging(Order $order): void
    {
        // Deduct stock when packaging actually begins
        if ($order->status === 'pending') {
            foreach ($order->orderItems as $item) {
                $this->adjustStock(
                    product: $item->product,
                    change: -$item->quantity,
                    type: 'packaging_started',
                    notes: "Packaging started for order #{$order->order_number}"
                );
            }
            $order->update(['status' => 'in_progress']);
        }
    }
}
