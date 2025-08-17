<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\StockService;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HandleOrderStatusChange
{
    public function __construct(private StockService $stockService)
    {}

    public function handle(OrderStatusUpdated $event): void
    {
        DB::transaction(function () use ($event) {
            // Adjust stock levels
            $this->stockService->handleOrderStatusChange(
                $event->order,
                $event->oldStatus,
                $event->newStatus
            );

            // Log order status change
            Log::create([
                'user_id' => Auth::id() ?? $event->order->user_id,
                'order_id' => $event->order->id,
                'action' => 'order_status_changed',
                'description' => "Order status changed from {$event->oldStatus} to {$event->newStatus}",
                'message' => "Order #{$event->order->order_number} status: {$event->oldStatus} → {$event->newStatus}",
                'type' => 'order',
                'created_at' => now(),
            ]);
        });
    }
}