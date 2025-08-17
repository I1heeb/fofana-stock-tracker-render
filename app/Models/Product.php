<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'minimum_stock',
        'low_stock_threshold',
        'sku',
        'supplier_id',
        'barcode',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'minimum_stock' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    protected $attributes = [
        'low_stock_threshold' => 10,
        'stock_quantity' => 0,
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isLowStock()
    {
        // S'assurer qu'on a des valeurs numériques
        $stock = (int) ($this->stock_quantity ?? 0);
        $threshold = (int) ($this->low_stock_threshold ?? 10);

        // Stock faible = stock > 0 ET stock <= seuil
        return $stock > 0 && $stock <= $threshold;
    }
    
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }
    
    public function getStockStatusClass(): string
    {
        if ($this->stock_quantity == 0) {
            return 'bg-red-100 text-red-800'; // Out of stock
        }
        
        if ($this->isLowStock()) {
            return 'bg-yellow-100 text-yellow-800'; // Low stock
        }
        
        return 'bg-green-100 text-green-800'; // Normal stock
    }
}


