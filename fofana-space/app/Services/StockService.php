<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjustStock(Product $product, int $change, string $type, ?string $notes = null, ?Order $order = null): StockHistory
    {
        return DB::transaction(function () use ($product, $change, $type, $notes, $order) {
            // Update product stock
            $product->stock += $change;
            
            if ($product->stock < 0) {
                throw new \InvalidArgumentException("Stock cannot be negative for product {$product->id}");
            }
            
            $product->save();

            // Create history record
            return StockHistory::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'order_id' => $order?->id,
                'change' => $change,
                'balance' => $product->stock,
                'type' => $type,
                'notes' => $notes
            ]);
        });
    }

    public function handleOrderStatusChange(Order $order, string $oldStatus, string $newStatus): void
    {
        // Handle stock reduction when order is marked as out
        if ($newStatus === 'out' && $oldStatus !== 'out') {
            foreach ($order->orderItems as $item) {
                $this->adjustStock(
                    product: $item->product,
                    change: -$item->quantity,
                    type: 'order_out',
                    notes: "Order #{$order->id} shipped",
                    order: $order
                );
            }
        }

        // Handle stock restoration when order is canceled or returned
        if (in_array($newStatus, ['canceled', 'returned']) && !in_array($oldStatus, ['canceled', 'returned'])) {
            foreach ($order->orderItems as $item) {
                $this->adjustStock(
                    product: $item->product,
                    change: $item->quantity,
                    type: $newStatus === 'canceled' ? 'order_canceled' : 'order_return',
                    notes: "Order #{$order->id} {$newStatus}",
                    order: $order
                );
            }
        }
    }

    public function manualAdjustment(Product $product, int $change, string $notes): StockHistory
    {
        return $this->adjustStock(
            product: $product,
            change: $change,
            type: 'adjustment',
            notes: $notes
        );
    }
} 