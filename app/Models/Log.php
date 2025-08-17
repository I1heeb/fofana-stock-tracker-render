<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'order_id',
        'product_id',
        'action',
        'description',
        'message',
        'type',
        'quantity',
        'created_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getOrderUrlAttribute(): ?string
    {
        return $this->order_id ? route('orders.show', $this->order_id) : null;
    }

    public function getProductUrlAttribute(): ?string
    {
        return $this->product_id ? route('products.show', $this->product_id) : null;
    }
}






