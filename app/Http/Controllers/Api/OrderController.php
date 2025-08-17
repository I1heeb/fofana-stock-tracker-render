<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'orderItems.product'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function($q, $search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20);
            
        return response()->json($orders);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'logs']);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = DB::transaction(function () use ($request) {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'status' => Order::STATUS_IN_PROGRESS,
                ]);

                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    
                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \Exception("Insufficient stock for {$product->name}");
                    }

                    $order->orderItems()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);
                }

                event(new OrderStatusUpdated($order, null, Order::STATUS_IN_PROGRESS));
                return $order->load(['orderItems.product']);
            });

            return response()->json($order, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:in_progress,packed,out,canceled,returned'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        event(new OrderStatusUpdated($order, $oldStatus, $request->status));

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['orderItems.product'])
        ]);
    }
}