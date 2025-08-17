<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\StockHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ChartController extends Controller
{
    public function getKpis(Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Total orders this period
        $totalOrders = Order::where('created_at', '>=', $startDate)->count();

        // Average fulfillment time (time between creation and 'out' status)
        $avgFulfillmentTime = Order::where('status', 'out')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours'))
            ->first()
            ->avg_hours ?? 0;

        // Current stock value
        $stockValue = Product::sum(DB::raw('stock * price'));

        // Low stock items count
        $lowStockCount = Product::whereRaw('stock <= COALESCE(threshold, 10)')->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'avg_fulfillment_hours' => round($avgFulfillmentTime, 1),
            'stock_value' => $stockValue,
            'low_stock_count' => $lowStockCount
        ]);
    }

    public function getDrilldownData(Request $request)
    {
        $date = $request->input('date');
        $status = $request->input('status');
        $productId = $request->input('product_id');
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        
        $query = Order::with(['user', 'orderItems.product'])
            ->whereDate('created_at', $date);

        if ($status) {
            $query->where('status', $status);
        }

        if ($productId) {
            $query->whereHas('orderItems', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        // Add sorting
        $query->orderBy($sortBy, $sortDir);

        // Get total count for pagination
        $total = $query->count();

        // Add pagination
        $orders = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'status' => ucfirst(str_replace('_', ' ', $order->status)),
                    'user' => $order->user->name,
                    'products' => $order->orderItems->map(function ($item) {
                        return [
                            'name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price
                        ];
                    }),
                    'total_items' => $order->orderItems->sum('quantity'),
                    'total_value' => $order->orderItems->sum(function ($item) {
                        return $item->quantity * $item->price;
                    })
                ];
            });

        return response()->json([
            'data' => $orders,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ]);
    }

    public function exportDrilldownData(Request $request)
    {
        $date = $request->input('date');
        $status = $request->input('status');
        $productId = $request->input('product_id');
        $format = $request->input('format', 'csv');
        
        $query = Order::with(['user', 'orderItems.product'])
            ->whereDate('created_at', $date);

        if ($status) {
            $query->where('status', $status);
        }

        if ($productId) {
            $query->whereHas('orderItems', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $orders = $query->get();

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="drilldown-' . $date . '.csv"',
            ];

            $handle = fopen('php://temp', 'r+');
            
            // Add headers
            fputcsv($handle, [
                'Time',
                'Order ID',
                'Bordereau Number',
                'Status',
                'User',
                'Product',
                'Quantity',
                'Price',
                'Total Value'
            ]);

            // Add data rows
            foreach ($orders as $order) {
                foreach ($order->orderItems as $item) {
                    fputcsv($handle, [
                        $order->created_at->format('Y-m-d H:i:s'),
                        $order->id,
                        $order->bordereau_number ?? 'N/A',
                        $order->status,
                        $order->user->name,
                        $item->product->name,
                        $item->quantity,
                        $item->price,
                        $item->quantity * $item->price
                    ]);
                }
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            return Response::make($content, 200, $headers);
        }

        // Add support for other formats (e.g., Excel) here if needed
        return response()->json(['error' => 'Unsupported format'], 400);
    }

    public function ordersByStatus(Request $request)
    {
        $days = $request->input('days', 30);
        $groupBy = $request->input('group_by', 'day'); // day, week, month
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $orders = Order::select(
            DB::raw("DATE_FORMAT(created_at, '$dateFormat') as date"),
            'status',
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        $dates = [];
        $statusData = [
            'in_progress' => [],
            'packed' => [],
            'out' => [],
            'canceled' => [],
            'returned' => []
        ];

        // Initialize all dates with 0 counts
        for ($i = 0; $i <= $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dates[] = $date;
            foreach ($statusData as &$data) {
                $data[$date] = 0;
            }
        }

        // Fill in actual counts
        foreach ($orders as $order) {
            $statusData[$order->status][$order->date] = $order->count;
        }

        // Prepare datasets
        $datasets = [];
        $colors = [
            'in_progress' => 'rgb(59, 130, 246)', // Blue
            'packed' => 'rgb(16, 185, 129)', // Green
            'out' => 'rgb(245, 158, 11)', // Yellow
            'canceled' => 'rgb(239, 68, 68)', // Red
            'returned' => 'rgb(107, 114, 128)' // Gray
        ];

        foreach ($statusData as $status => $data) {
            $datasets[] = [
                'label' => ucfirst(str_replace('_', ' ', $status)),
                'data' => array_values($data),
                'borderColor' => $colors[$status],
                'backgroundColor' => $colors[$status],
                'tension' => 0.1
            ];
        }

        return response()->json([
            'dates' => $dates,
            'datasets' => $datasets
        ]);
    }

    public function stockLevels(Request $request)
    {
        $days = $request->input('days', 30);
        $groupBy = $request->input('group_by', 'day');
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $productIds = $request->input('product_ids', []);

        $dateFormat = match($groupBy) {
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $query = StockHistory::select(
            'product_id',
            DB::raw("DATE_FORMAT(created_at, '$dateFormat') as date"),
            DB::raw('MAX(balance) as balance')
        )
            ->where('created_at', '>=', $startDate)
            ->groupBy('product_id', 'date')
            ->orderBy('date');

        if (!empty($productIds)) {
            $query->whereIn('product_id', $productIds);
        } else {
            // If no products specified, get top 5 by transaction volume
            $topProducts = StockHistory::select('product_id')
                ->groupBy('product_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(5)
                ->pluck('product_id');
            $query->whereIn('product_id', $topProducts);
        }

        $stockHistory = $query->get();
        $products = Product::whereIn('id', $stockHistory->pluck('product_id')->unique())
            ->get()
            ->keyBy('id');

        // Prepare datasets
        $dates = [];
        $productData = [];

        // Initialize dates array
        for ($i = 0; $i <= $days; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
        }

        // Initialize product data
        foreach ($products as $product) {
            $productData[$product->id] = [
                'name' => $product->name,
                'data' => array_fill(0, count($dates), null)
            ];
        }

        // Fill in actual data
        foreach ($stockHistory as $record) {
            $dateIndex = array_search($record->date, $dates);
            if ($dateIndex !== false) {
                $productData[$record->product_id]['data'][$dateIndex] = $record->balance;
            }
        }

        // Interpolate missing values
        foreach ($productData as &$data) {
            $lastValue = null;
            for ($i = 0; $i < count($data['data']); $i++) {
                if ($data['data'][$i] === null) {
                    $data['data'][$i] = $lastValue;
                } else {
                    $lastValue = $data['data'][$i];
                }
            }
        }

        // Prepare final datasets
        $datasets = [];
        $colors = [
            'rgb(59, 130, 246)', // Blue
            'rgb(16, 185, 129)', // Green
            'rgb(245, 158, 11)', // Yellow
            'rgb(239, 68, 68)', // Red
            'rgb(107, 114, 128)' // Gray
        ];

        $colorIndex = 0;
        foreach ($productData as $data) {
            $datasets[] = [
                'label' => $data['name'],
                'data' => $data['data'],
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => $colors[$colorIndex % count($colors)],
                'tension' => 0.1
            ];
            $colorIndex++;
        }

        return response()->json([
            'dates' => $dates,
            'datasets' => $datasets
        ]);
    }

    public function lowStockAlerts()
    {
        $products = Product::select('products.*')
            ->selectRaw('COALESCE(threshold, 10) as threshold')
            ->whereRaw('stock <= COALESCE(threshold, 10)')
            ->orderBy('stock')
            ->get();

        $data = [
            'products' => $products->pluck('name')->toArray(),
            'currentStock' => $products->pluck('stock')->toArray(),
            'thresholds' => $products->pluck('threshold')->toArray(),
            'colors' => $products->map(function ($product) {
                $ratio = $product->stock / $product->threshold;
                if ($ratio <= 0.25) return 'rgba(239, 68, 68, 0.2)'; // Critical - Red
                if ($ratio <= 0.5) return 'rgba(245, 158, 11, 0.2)'; // Warning - Yellow
                return 'rgba(59, 130, 246, 0.2)'; // Low - Blue
            })->toArray(),
            'borderColors' => $products->map(function ($product) {
                $ratio = $product->stock / $product->threshold;
                if ($ratio <= 0.25) return 'rgb(239, 68, 68)';
                if ($ratio <= 0.5) return 'rgb(245, 158, 11)';
                return 'rgb(59, 130, 246)';
            })->toArray()
        ];

        return response()->json($data);
    }
} 