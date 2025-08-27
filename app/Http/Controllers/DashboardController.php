<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    const CACHE_TTL = 300; // 5 minutes
    const RECENT_ACTIVITIES_LIMIT = 5;

    public function index()
    {
        // Redirect admins to their proper dashboards
        $user = auth()->user();

        if ($user->email === 'nour@gmail.com') {
            return redirect()->route('admin.nour.dashboard');
        }

        if (in_array($user->email, ['iheb@admin.com', 'aaaa@dev.com']) || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        try {
            $stats = [
                'total_orders' => $this->getTotalOrders(),
                'pending_orders' => $this->getPendingOrders(),
                'total_products' => $this->getTotalProducts(),
                'low_stock_products' => $this->getLowStockCount(),

                'today_orders' => $this->getTodayOrders(),
                'revenue_today' => $this->getTodayRevenue(),
            ];
        } catch (\Exception $e) {
            \Log::error('Dashboard error: ' . $e->getMessage());

            $stats = [
                'error' => 'Unable to load dashboard data. Please try again.',
                'total_orders' => 0,
                'pending_orders' => 0,
                'total_products' => 0,
                'low_stock_products' => 0,
                'today_orders' => 0,
                'revenue_today' => 0.0,
            ];
        }

        return view('dashboard', compact('stats'));
    }

    private function getTotalOrders()
    {
        return Order::count();
    }

    private function getPendingOrders()
    {
        return Order::whereNotIn('status', ['out', 'returned', 'cancelled', 'completed'])->count();
    }

    private function getTotalProducts()
    {
        return Product::count();
    }

    private function getLowStockCount()
    {
        $allProducts = Product::all();

        return $allProducts->filter(function ($product) {
            $stock = (int) ($product->stock_quantity ?? 0);
            $threshold = (int) ($product->low_stock_threshold ?? 10);

            // Inclure les produits en stock faible ET en rupture
            return $stock <= $threshold;
        })->count();
    }

    private function getTodayOrders()
    {
        return Order::whereDate('created_at', today())->count();
    }

    private function getTodayRevenue()
    {
        return Order::whereDate('created_at', today())
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->sum('total_amount') ?? 0.0;
    }
}