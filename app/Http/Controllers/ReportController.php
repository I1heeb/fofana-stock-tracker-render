<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('total_amount'),
        ];

        return view('reports.index', compact('stats'));
    }
    public function stockMovementReport(Request $request)
{
    $period = $request->get('period', 30);
    
    $movements = Log::where('type', 'stock')
                   ->where('created_at', '>=', now()->subDays($period))
                   ->with(['product', 'user'])
                   ->get()
                   ->groupBy('product_id');
    
    return view('reports.stock-movement', compact('movements', 'period'));
}
    public function export(Request $request): Response
    {
        $type = $request->get('type', 'orders');
        
        return match($type) {
            'orders' => $this->exportOrders(),
            'products' => $this->exportProducts(),
            'logs' => $this->exportLogs(),
            default => $this->exportOrders(),
        };
    }

    private function exportOrders(): Response
    {
        return response()->streamDownload(function() {
            echo "Order ID,Order Number,Bordereau Number,User,Status,Total Amount,Created At\n";

            Order::with('user')->chunk(100, function($orders) {
                foreach ($orders as $order) {
                    echo sprintf(
                        "%d,%s,%s,%s,%s,%.2f,%s\n",
                        $order->id,
                        $order->order_number,
                        $order->bordereau_number ?? 'N/A',
                        $order->user->name ?? 'N/A',
                        $order->status,
                        $order->total_amount ?? 0,
                        $order->created_at->format('Y-m-d H:i:s')
                    );
                }
            });
        }, 'orders_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    private function exportProducts(): Response
    {
        return response()->streamDownload(function() {
            echo "Product ID,Name,SKU,Price,Stock Quantity,Low Stock Threshold,Status\n";
            
            Product::chunk(100, function($products) {
                foreach ($products as $product) {
                    $status = $product->isLowStock() ? 'Low Stock' : 'Normal';
                    echo sprintf(
                        "%d,%s,%s,%.2f,%d,%d,%s\n",
                        $product->id,
                        '"' . str_replace('"', '""', $product->name) . '"',
                        $product->sku,
                        $product->price,
                        $product->stock_quantity,
                        $product->low_stock_threshold,
                        $status
                    );
                }
            });
        }, 'products_' . now()->format('Ymd_His') . '.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_' . now()->format('Ymd_His') . '.csv"',
        ]);
    }

    public function logs(Request $request)
    {
        $query = Log::with(['user', 'product', 'order'])
            ->when($request->filled('from'), function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->from);
            })
            ->when($request->filled('to'), function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->to);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                return $q->where(function ($query) use ($request) {
                    $query->where('action', 'like', '%' . $request->search . '%')
                          ->orWhere('description', 'like', '%' . $request->search . '%')
                          ->orWhere('message', 'like', '%' . $request->search . '%');
                });
            });

        $logs = $query->latest()->paginate(20);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('reports.logs', compact('logs', 'users'));
    }

    public function exportLogs(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        // Apply same filters as logs method
        $query = Log::with(['user', 'product', 'order'])
            ->when($request->filled('from'), function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->from);
            })
            ->when($request->filled('to'), function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->to);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->filled('user_id'), function ($q) use ($request) {
                return $q->where('user_id', $request->user_id);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                return $q->where(function ($query) use ($request) {
                    $query->where('action', 'like', '%' . $request->search . '%')
                          ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            });

        $logs = $query->latest()->get();

        if ($format === 'pdf') {
            return $this->exportLogsPDF($logs);
        }

        return $this->exportLogsCSV($logs);
    }

    private function exportLogsCSV($logs)
    {
        $filename = 'logs_' . now()->format('Y-m-d_His') . '.csv';
        
        return response()->streamDownload(function() use ($logs) {
            echo "Time,User,Action,Type,Description\n";
            
            foreach ($logs as $log) {
                echo sprintf(
                    "%s,%s,%s,%s,%s\n",
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user->name ?? 'System',
                    $log->action,
                    $log->type ?? 'info',
                    '"' . str_replace('"', '""', $log->description ?? $log->message) . '"'
                );
            }
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}

