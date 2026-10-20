<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    public const STATUSES = ['new', 'review', 'approved', 'rejected', 'replaced'];

    protected $fillable = [
        'number', 'customer_id', 'bot_user_id', 'order_id', 'order_item_id',
        'product_id', 'shop_id', 'seller_id', 'assignee_id', 'reason',
        'description', 'status', 'reject_reason', 'replacement_cost',
        'replacement_product', 'photos', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'replacement_cost' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ClaimStatusLog::class);
    }

    /** Soʻrovdagi barcha mahsulot nomlari (1 ta yoki koʻp). */
    public function productLabels(): array
    {
        $this->loadMissing(['items.orderItem', 'items.product', 'orderItem', 'product']);

        if ($this->items->isNotEmpty()) {
            return $this->items
                ->map(fn (ClaimItem $i) => $i->displayName())
                ->filter()
                ->values()
                ->all();
        }

        $one = $this->product?->name ?? $this->orderItem?->name;

        return $one ? [$one] : ['Mahsulot'];
    }

    public function productSummary(): string
    {
        $labels = $this->productLabels();
        if (count($labels) <= 1) {
            return $labels[0] ?? 'Mahsulot';
        }

        return count($labels).' ta: '.implode('; ', $labels);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, ['rejected', 'replaced'], true);
    }

    public function daysOpen(): int
    {
        $end = $this->closed_at ?? now();

        return (int) $this->created_at->diffInDays($end);
    }
}
