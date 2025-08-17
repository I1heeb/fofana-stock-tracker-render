<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessStockUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Product $product,
        private int $oldStock,
        private int $newStock
    ) {}

    public function handle(StockService $stockService): void
    {
        // Traitement automatique des mises à jour de stock
        if ($this->product->isLowStock()) {
            $stockService->autoRestock($this->product);
        }

        // Autres traitements automatiques...
    }
}