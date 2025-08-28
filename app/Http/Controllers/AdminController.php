<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        // Middleware is handled by routes, no need for duplicate checks
    }

    /**
     * Display the admin dashboard
     */
    public function dashboard()
    {
        // Show admin dashboard with comprehensive stats
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_products' => \App\Models\Product::count(),
            'total_orders' => \App\Models\Order::count(),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            'total_revenue' => \App\Models\Order::where('status', 'completed')->sum('total_amount') ?? 0,
            'low_stock_products' => \App\Models\Product::whereRaw('stock_quantity <= low_stock_threshold')->count(),
            'recent_orders' => \App\Models\Order::with('user')->latest()->take(5)->get(),
            'low_stock_products_list' => \App\Models\Product::whereRaw('stock_quantity <= low_stock_threshold')->take(5)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Display all users
     */
    public function users()
    {
        $users = User::paginate(20);
        return view('admin.users', compact('users'));
    }

    /**
     * Display all products
     */
    public function products()
    {
        try {
            \Log::info('AdminController::products - Starting method');

            $products = Product::paginate(20);
            \Log::info('AdminController::products - Products loaded', ['count' => $products->count()]);

            \Log::info('AdminController::products - Attempting to load view: admin.products');
            return view('admin.products', compact('products'));

        } catch (\Exception $e) {
            \Log::error('AdminController::products - Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple response for debugging
            return response()->json([
                'error' => 'Admin products error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Display all orders
     */
    public function orders()
    {
        try {
            \Log::info('AdminController::orders - Starting method');

            $orders = Order::with('user')->latest()->paginate(20);
            \Log::info('AdminController::orders - Orders loaded', ['count' => $orders->count()]);

            \Log::info('AdminController::orders - Attempting to load view: admin.orders');
            return view('admin.orders', compact('orders'));

        } catch (\Exception $e) {
            \Log::error('AdminController::orders - Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Admin orders error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Display system reports
     */
    public function reports()
    {
        try {
            \Log::info('AdminController::reports - Starting method');

            $reports = [
                'daily_sales' => Order::whereDate('created_at', today())
                    ->where('status', 'completed')
                    ->sum('total_amount') ?? 0,
                'weekly_sales' => Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->where('status', 'completed')
                    ->sum('total_amount') ?? 0,
                'monthly_sales' => Order::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'completed')
                    ->sum('total_amount') ?? 0,
                'top_products' => DB::table('order_items')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', 'completed')
                    ->select('products.name', 'products.sku', DB::raw('SUM(order_items.quantity) as total_sold'))
                    ->groupBy('products.id', 'products.name', 'products.sku')
                    ->orderBy('total_sold', 'desc')
                    ->take(10)
                    ->get(),
            ];

            // Add additional statistics
            $reports['total_orders'] = Order::count();
            $reports['completed_orders'] = Order::where('status', 'completed')->count();
            $reports['pending_orders'] = Order::where('status', 'pending')->count();
            $reports['total_products'] = Product::count();
            $reports['low_stock_products'] = Product::whereRaw('stock_quantity <= low_stock_threshold')->count();

            \Log::info('AdminController::reports - Reports data compiled', ['reports_keys' => array_keys($reports)]);

            \Log::info('AdminController::reports - Attempting to load view: admin.reports');
            return view('admin.reports', compact('reports'));

        } catch (\Exception $e) {
            \Log::error('AdminController::reports - Error occurred', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Admin reports error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,service_client,packaging_agent'
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        return view('admin.create-user');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,service_client,packaging_agent'
        ]);

        $permissions = [];
        if ($request->role === 'admin') {
            $permissions = ['manage_users', 'manage_products', 'manage_orders', 'view_reports', 'manage_suppliers'];
        } elseif ($request->role === 'packaging_agent') {
            $permissions = ['manage_orders', 'manage_products'];
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'permissions' => $permissions,
        ]);

        return redirect()->back()->with('success', 'User created successfully.');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        $currentUser = Auth::user();
        
        // Only admins and super admins can delete users
        if (!$currentUser->isAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Cannot delete yourself
        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        // Super admins cannot be deleted
        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Super admin cannot be deleted.');
        }

        // Regular admins can only delete packaging agents and service clients
        if ($user->isAdmin() && !$currentUser->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Only super admin can delete other admins.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    /**
     * Make user super admin
     */
    public function makeSuperAdmin(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Only super admin can promote other users to super admin.');
        }

        if (!$user->isAdmin()) {
            return redirect()->back()->with('error', 'User must be an admin first.');
        }

        $user->update(['is_super_admin' => true]);

        return redirect()->back()->with('success', $user->name . ' is now a super admin.');
    }

    /**
     * Remove super admin privileges
     */
    public function removeSuperAdmin(User $user)
    {
        $currentUser = Auth::user();

        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Only super admin can remove super admin privileges.');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'You cannot remove your own super admin privileges.');
        }

        $user->update(['is_super_admin' => false]);

        return redirect()->back()->with('success', $user->name . ' is no longer a super admin.');
    }
}




