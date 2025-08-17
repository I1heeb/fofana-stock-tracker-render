<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function dashboard()
    {
        return view('reports.dashboard', [
            'products' => Product::orderBy('name')->get(['id', 'name'])
        ]);
    }

    public function orders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:in_progress,packed,out,canceled,returned',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'product' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:10|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = Order::query()
            ->with(['orderItems.product', 'user'])
            ->when($request->filled('status'), function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->filled(['from', 'to']), function ($q) use ($request) {
                return $q->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to . ' 23:59:59'
                ]);
            })
            ->when($request->filled('product'), function ($q) use ($request) {
                return $q->whereHas('orderItems.product', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->product . '%')
                        ->orWhere('sku', 'like', '%' . $request->product . '%');
                });
            });

        $orders = $query->latest()->paginate($request->input('per_page', 20));

        return view('reports.orders', [
            'orders' => $orders,
            'statuses' => ['in_progress', 'packed', 'out', 'canceled', 'returned']
        ]);
    }

    public function exportOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:in_progress,packed,out,canceled,returned',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'product' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $query = Order::query()
            ->with(['orderItems.product', 'user'])
            ->when($request->filled('status'), function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->filled(['from', 'to']), function ($q) use ($request) {
                return $q->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to . ' 23:59:59'
                ]);
            })
            ->when($request->filled('product'), function ($q) use ($request) {
                return $q->whereHas('orderItems.product', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->product . '%')
                        ->orWhere('sku', 'like', '%' . $request->product . '%');
                });
            });

        $orders = $query->latest()->get();

        $csvFileName = 'orders_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $csvFileName . '"',
        ];

        $handle = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($handle, [
            'Order ID',
            'Bordereau Number',
            'Created At',
            'Status',
            'User',
            'Products',
            'Total Items',
            'Notes'
        ]);

        // Add data rows
        foreach ($orders as $order) {
            $products = $order->orderItems->map(function ($item) {
                return sprintf('%s (x%d)', $item->product->name, $item->quantity);
            })->join(', ');

            fputcsv($handle, [
                $order->id,
                $order->bordereau_number ?? 'N/A',
                $order->created_at->format('Y-m-d H:i:s'),
                ucfirst(str_replace('_', ' ', $order->status)),
                $order->user->name,
                $products,
                $order->orderItems->sum('quantity'),
                $order->notes
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make($content, 200, $headers);
    }
} 