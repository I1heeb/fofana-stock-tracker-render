<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Log;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()->with('supplier');

        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0)
                          ->whereRaw('stock_quantity > COALESCE(low_stock_threshold, 10)');
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 10)')
                          ->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '=', 0);
                    break;
            }
        }

        // Filter by low stock (legacy support)
        if ($request->filled('low_stock') || $request->get('filter') === 'low_stock') {
            $query->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 10)');
        }

        // Date range filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Price range filters
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created');
        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'price':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'stock':
                $query->orderBy('stock_quantity', 'asc');
                break;
            case 'stock_desc':
                $query->orderBy('stock_quantity', 'desc');
                break;
            case 'created':
                $query->orderBy('created_at', 'desc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(15)->appends(request()->query());
        $totalProducts = Product::count();

        return view('products.index', compact('products', 'totalProducts'));
    }

    public function lowStock(Request $request): View
    {
        // Approche plus simple : requête SQL directe
        $query = Product::query()->with('supplier')
            ->where(function($q) {
                $q->whereRaw('stock_quantity <= COALESCE(low_stock_threshold, 10)');
            });

        // Search by name or SKU
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('stock_quantity', 'asc')
            ->paginate(10)
            ->appends(request()->query());

        return view('products.low-stock', compact('products'));
    }

    public function create(): View
    {
        // Seul l'admin peut créer des produits
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Seul l\'administrateur peut créer des produits.');
        }

        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Seul l'admin peut créer des produits
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Seul l\'administrateur peut créer des produits.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'barcode' => 'nullable|string',
        ]);

        Product::create($validated);

        if ($request->has('create_and_add_another')) {
            return redirect()->route('products.create')
                ->with('success', 'Produit créé avec succès. Ajoutez-en un autre !');
        }

        return redirect()->route('products.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Product $product): View
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        // Seul l'admin peut éditer les produits
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Seul l\'administrateur peut éditer les produits.');
        }

        return view('products.edit', compact('product'));
    }
    
    public function update(Request $request, Product $product): RedirectResponse
    {
        // Seul l'admin peut mettre à jour les produits
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Seul l\'administrateur peut modifier les produits.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Seul l'admin peut supprimer des produits
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Seul l\'administrateur peut supprimer des produits.');
        }

        try {
            // Check if product has related records that would prevent deletion
            $hasOrderItems = $product->orderItems()->exists();
            $hasPurchaseOrderItems = $product->purchaseOrderItems()->exists();
            $hasLogs = $product->logs()->exists();
            $hasStockHistories = $product->stockHistories()->exists();

            if ($hasOrderItems || $hasPurchaseOrderItems || $hasLogs || $hasStockHistories) {
                return redirect()->route('products.index')
                    ->with('error', 'Cannot delete product. It has associated order items, purchase orders, logs, or stock history records.');
            }

            $product->delete();

            return redirect()->route('products.index')
                ->with('success', 'Produit supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->route('products.index')
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        // Admin et packaging agent peuvent gérer le stock
        if (!in_array(auth()->user()->role, ['admin', 'packaging_agent'])) {
            return back()->withErrors(['error' => '❌ ACCÈS REFUSÉ: Seuls les administrateurs et agents de packaging peuvent modifier le stock.']);
        }

        $request->validate([
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
        ]);

        $oldStock = $product->stock_quantity;
        $adjustment = $request->adjustment;
        $product->stock_quantity += $adjustment;
        
        // Empêcher le stock négatif pour les non-admins (sécurité supplémentaire)
        if ($product->stock_quantity < 0 && !auth()->user()->isAdmin()) {
            return back()->withErrors(['adjustment' => '❌ Le stock ne peut pas être négatif.']);
        }
        
        $product->save();

        // Log de l'ajustement
        Log::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'action' => $adjustment > 0 ? 'stock_increased' : 'stock_decreased',
            'description' => 'Manual stock adjustment by admin',
            'message' => "Stock for {$product->name} adjusted from {$oldStock} to {$product->stock_quantity}. Reason: {$request->reason}",
            'type' => 'admin',
            'quantity' => $adjustment,
            'created_at' => now(),
        ]);

        $actionText = $adjustment > 0 ? 'rechargé' : 'réduit';
        return redirect()->back()
            ->with('success', "✅ Stock {$actionText} avec succès! {$oldStock} → {$product->stock_quantity}");
    }
}

