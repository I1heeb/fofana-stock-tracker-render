<?php

namespace App\Http\Controllers;

use App\Events\OrderStatusUpdated;
use App\Http\Requests\BulkUpdateStatusRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function bulkUpdateStatus(BulkUpdateStatusRequest $request)
    {
        $validated = $request->validated();
        $successCount = 0;
        $failedOrders = [];

        DB::beginTransaction();
        try {
            $orders = Order::findMany($validated['order_ids']);

            foreach ($orders as $order) {
                try {
                    $oldStatus = $order->status;
                    $order->status = $validated['status'];
                    $order->save();

                    // Dispatch event for each order
                    event(new OrderStatusUpdated($order, $oldStatus));
                    $successCount++;
                } catch (\Exception $e) {
                    $failedOrders[] = [
                        'id' => $order->id,
                        'error' => 'Failed to update status'
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'message' => "{$successCount} orders updated successfully",
                'success_count' => $successCount,
                'failed_orders' => $failedOrders,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 