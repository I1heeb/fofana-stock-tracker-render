<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Support\Facades\Notification;

class HandleLowStockNotification
{
    /**
     * Handle the event.
     */
    public function handle(LowStockDetected $event): void
    {
        // Get all packaging users to notify
        $packagingUsers = User::where('role', 'packaging')->get();

        // Send notification to all packaging users
        Notification::send($packagingUsers, new LowStockAlert(
            $event->product,
            $event->currentStock,
            $event->threshold
        ));
    }
} 