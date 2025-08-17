<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'change',
        'balance',
        'type',
        'user_id',
        'order_id',
        'notes'
    ];

    protected $casts = [
        'change' => 'integer',
        'balance' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public const TYPES = [
        'adjustment' => 'Manual Adjustment',
        'order_out' => 'Order Shipped',
        'order_return' => 'Order Returned',
        'restock' => 'Restock'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getTypeTextAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getChangeTextAttribute(): string
    {
        return ($this->change > 0 ? '+' : '') . $this->change;
    }
} 