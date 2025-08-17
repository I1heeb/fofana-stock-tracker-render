<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Events\LowStockDetected;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStockLevels
{
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier les stocks faibles toutes les 10 minutes
        $lastCheck = cache('last_stock_check', 0);
        
        if (now()->timestamp - $lastCheck > 600) { // 10 minutes
            $this->checkLowStock();
            cache(['last_stock_check' => now()->timestamp], 600);
        }

        return $next($request);
    }

    private function checkLowStock(): void
    {
        Product::where('stock_quantity', '<=', \DB::raw('low_stock_threshold'))
            ->chunk(50, function ($products) {
                foreach ($products as $product) {
                    event(new LowStockDetected(
                        $product,
                        $product->stock_quantity,
                        $product->low_stock_threshold
                    ));
                }
            });
    }
}