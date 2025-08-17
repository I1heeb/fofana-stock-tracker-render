<?php

namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $description, $type = 'info')
    {
        try {
            Log::create([
                'action' => $action,
                'description' => $description,
                'type' => $type,
                'user_id' => Auth::id(),
                'created_at' => now(),
                //Add message field
                'type' => $type,
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Activity logging failed: ' . $e->getMessage());
        }
    }
}