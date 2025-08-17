<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'bordereau_number',
        'status',
        'total_amount',
        'notes',
    ];

    // Update Order status constants to match FR7
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_PACKED = 'packed';
    public const STATUS_OUT = 'out';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_RETURNED = 'returned';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_PACKED => 'Packed',
            self::STATUS_OUT => 'Out',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    /**
     * Get reservation states (stock should be deducted)
     */
    public static function getReservationStates(): array
    {
        return [
            self::STATUS_IN_PROGRESS,
            self::STATUS_PACKED,
        ];
    }

    /**
     * Check if status is a reservation state
     */
    public function isReservationState(string $status = null): bool
    {
        $status = $status ?? $this->status;
        return in_array($status, self::getReservationStates());
    }

    /**
     * Check if status is a revert state
     */
    public function isRevertState(string $status = null): bool
    {
        $status = $status ?? $this->status;
        return in_array($status, self::getRevertStates());
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }
}



