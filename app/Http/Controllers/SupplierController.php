<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount(['products', 'purchaseOrders'])
            ->orderBy('name')
            ->paginate(20);
            
        return view('suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['products', 'purchaseOrders.items']);
        return view('suppliers.show', compact('supplier'));
    }

    public function create()
    {
        // Service client ne peut pas créer de fournisseurs
        if (auth()->user()->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les fournisseurs.');
        }

        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        // Service client ne peut pas éditer de fournisseurs
        if (auth()->user()->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les fournisseurs.');
        }

        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        // Service client ne peut pas modifier de fournisseurs
        if (auth()->user()->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les fournisseurs.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        // Service client ne peut pas supprimer de fournisseurs
        if (auth()->user()->role === 'service_client') {
            abort(403, 'Les clients service ne peuvent que consulter les fournisseurs.');
        }

        if ($supplier->products()->exists() || $supplier->purchaseOrders()->exists()) {
            return back()->with('error', 'Cannot delete supplier with associated products or purchase orders.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}