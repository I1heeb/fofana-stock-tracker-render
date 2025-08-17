<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ServiceClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:service_client']);
    }

    public function stockView(Request $request)
    {
        $products = Product::select(['name', 'sku', 'stock_quantity', 'low_stock_threshold'])
                          ->when($request->search, function($query, $search) {
                              $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('sku', 'like', "%{$search}%");
                          })
                          ->orderBy('name')
                          ->paginate(20);

        return view('service-client.stock-view', compact('products'));
    }
}

