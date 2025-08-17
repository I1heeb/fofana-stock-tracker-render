<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Log;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OrderController extends Controller
{
    use AuthorizesRequests;
    

    /**
     * Display a listing of orders.
     *
     * @param Request $request
     * @return View|StreamedResponse
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'orderItems.product']);

        // Recherche par numéro de commande
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('order_number', 'like', "%{$search}%");
        }

        // Recherche par statut
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Recherche par date de début
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Recherche par date de fin
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Recherche par utilisateur
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Recherche par numéro de bordereau
        if ($request->filled('bordereau_search')) {
            $query->where('bordereau_number', 'like', '%' . $request->bordereau_search . '%');
        }

        // Export CSV si demandé
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportOrdersCSV($query->get());
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        // Récupérer les utilisateurs pour le filtre
        $users = \App\Models\User::orderBy('name')->get();

        // Statuts disponibles
        $statuses = [
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'packed' => 'Emballé',
            'out' => 'Expédié',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            'returned' => 'Retourné'
        ];

        return view('orders.index', compact('orders', 'users', 'statuses'));
    }

    public function create(Request $request)
    {
        // Allow packaging agents and admins to create orders
        $user = auth()->user();
        if ($user->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les commandes.');
        }

        $query = Product::with('supplier');
        
        // Recherche par nom ou SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        $products = $query->orderBy('created_at', 'desc')->get();
            
        return view('orders.create', compact('products'));
    }
    public function store(Request $request): RedirectResponse
    {
        // Service client ne peut pas créer de commandes
        if (auth()->user()->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les commandes.');
        }

        // Validation du bordereau number (obligatoire, 12 chiffres, unique)
        $request->validate([
            'bordereau_number' => [
                'required',
                'string',
                'regex:/^\d{12}$/',
                'unique:orders,bordereau_number'
            ],
            'notes' => 'nullable|string|max:1000',
        ], [
            'bordereau_number.required' => 'Le numéro de bordereau est obligatoire.',
            'bordereau_number.regex' => 'Le numéro de bordereau doit contenir exactement 12 chiffres.',
            'bordereau_number.unique' => 'Ce numéro de bordereau existe déjà.',
        ]);

        // Filtrer les items avec quantité > 0
        $filteredItems = collect($request->items ?? [])
            ->filter(fn($item) => isset($item['quantity']) && $item['quantity'] > 0)
            ->values()
            ->toArray();

        if (empty($filteredItems)) {
            return back()->withErrors(['items' => 'At least one item with quantity > 0 is required.']);
        }

        // Validation personnalisée pour le stock
        foreach ($filteredItems as $index => $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                return back()->withErrors(['error' => 'Product not found.']);
            }

            // VÉRIFICATION DU STOCK - Seuls les agents packaging peuvent dépasser
            if ($product->stock_quantity < $item['quantity']) {
                if (auth()->user()->role !== 'packaging') {
                    return back()->withErrors([
                        'error' => "❌ STOCK INSUFFISANT pour {$product->name}!\n" .
                                  "Stock disponible: {$product->stock_quantity}\n" .
                                  "Quantité demandée: {$item['quantity']}\n" .
                                  "⚠️ Seuls les agents packaging peuvent dépasser le stock disponible."
                    ])->withInput();
                } else {
                    // Packaging role warning but allow
                    session()->flash('warning', "⚠️ ATTENTION: Vous dépassez le stock pour {$product->name} (Stock: {$product->stock_quantity}, Demandé: {$item['quantity']})");
                }
            }
        }

        try {
            $order = DB::transaction(function () use ($request, $filteredItems) {
                // Créer la commande avec bordereau manuel
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'order_number' => 'ORD-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
                    'bordereau_number' => $request->bordereau_number,
                    'status' => 'pending',
                    'notes' => $request->notes,
                ]);

                $totalAmount = 0;

                foreach ($filteredItems as $item) {
                    $product = Product::find($item['product_id']);
                    
                    // RÉDUIRE LE STOCK (même en négatif pour les admins)
                    $oldStock = $product->stock_quantity;
                    $product->stock_quantity -= $item['quantity'];
                    $product->save();

                    // Log avec indication si stock négatif
                    $stockStatus = $product->stock_quantity < 0 ? ' [STOCK NÉGATIF]' : '';
                    
                    Log::create([
                        'user_id' => auth()->id(),
                        'product_id' => $product->id,
                        'order_id' => $order->id,
                        'action' => 'stock_reserved',
                        'description' => 'Stock reserved for new order' . $stockStatus,
                        'message' => "Stock for {$product->name} reduced from {$oldStock} to {$product->stock_quantity} (Order #{$order->order_number})" . $stockStatus,
                        'type' => $product->stock_quantity < 0 ? 'warning' : 'stock',
                        'quantity' => -$item['quantity'],
                        'created_at' => now(),
                    ]);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ]);

                    $totalAmount += $product->price * $item['quantity'];
                }

                $order->update(['total_amount' => $totalAmount]);
                try {
                    CacheService::clearDashboardCache();
                } catch (\Exception $e) {
                    // Log the error but don't break order creation
                    \Log::warning('Cache clear failed during order creation: ' . $e->getMessage());
                }
                return $order;
            });

            return redirect()->route('orders.index')
                ->with('success', "✅ Order #{$order->order_number} created successfully! Stock updated.");

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => '❌ Erreur: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'orderItems.product', 'logs']);
        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        // Allow packaging agents and admins to edit orders
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isPackagingAgent()) {
            abort(403, 'Unauthorized - Only packaging agents and admins can edit orders');
        }
        
        return view('orders.edit', compact('order'));
    }
    
    public function update(Request $request, Order $order): RedirectResponse
    {
        // Allow packaging agents and admins to update orders
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isPackagingAgent()) {
            abort(403, 'Unauthorized - Only packaging agents and admins can update orders');
        }
        
        $request->validate([
            'status' => 'required|in:pending,processing,packed,out,completed,cancelled,returned',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update order
        $order->update([
            'status' => $newStatus,
            'notes' => $request->notes,
        ]);

        // Clear cache if status affects revenue
        if (in_array($newStatus, ['cancelled', 'returned']) || 
            in_array($oldStatus, ['cancelled', 'returned'])) {
            CacheService::clearDashboardCache();
        }

        return redirect()->route('orders.show', $order)
            ->with('success', "Order #{$order->order_number} updated successfully!");
    }

    public function pending(Request $request): View
    {
        $query = Order::with(['user', 'orderItems.product'])
            ->whereNotIn('status', ['out', 'returned', 'cancelled', 'completed'])
            ->whereHas('orderItems.product', function($query) {
                $query->where('stock_quantity', '>', 0);
            });

        // Apply search filters
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('bordereau_search')) {
            $query->where('bordereau_number', 'like', '%' . $request->bordereau_search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->latest()->paginate(15);

        return view('orders.pending', compact('orders'));
    }

    public function processReturn(Order $order): RedirectResponse
    {
        if ($order->status !== 'out') {
            return back()->withErrors(['error' => 'Only shipped orders can be returned']);
        }
        
        $order->update(['status' => 'returned']);
        
        return redirect()->route('orders.show', $order)
            ->with('success', "Order #{$order->order_number} returned successfully");
    }

    /**
     * Export orders to CSV
     */
    private function exportOrdersCSV($orders)
    {
        $filename = 'orders_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');

            // Headers CSV
            fputcsv($file, [
                'Numéro de commande',
                'Numéro de bordereau',
                'Client',
                'Statut',
                'Montant total',
                'Date de création',
                'Date de modification',
                'Nombre d\'articles',
                'Quantité totale',
                'Notes'
            ]);

            // Données
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number ?? 'ORD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT),
                    $order->bordereau_number ?? 'N/A',
                    $order->user->name ?? 'N/A',
                    $order->status,
                    number_format($order->total_amount ?? 0, 2),
                    $order->created_at->format('d/m/Y H:i'),
                    $order->updated_at->format('d/m/Y H:i'),
                    $order->orderItems ? $order->orderItems->count() : 0,
                    $order->orderItems ? $order->orderItems->sum('quantity') : 0,
                    $order->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}





