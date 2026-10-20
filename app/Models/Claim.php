<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Claim extends Model
{
    public const STATUSES = [
        'new', 'review', 'approved', 'assigned', 'en_route', 'delivered',
        'done', 'rejected', 'dispute', 'unconfirmed',
    ];

    public const CLOSED = ['done', 'rejected', 'unconfirmed'];

    protected $fillable = [
        'number', 'customer_id', 'bot_user_id', 'order_id', 'order_item_id',
        'product_id', 'shop_id', 'seller_id', 'assignee_id', 'courier_id',
        'reason', 'description', 'address', 'address_lat', 'address_lng',
        'status', 'reject_reason', 'replacement_cost', 'replacement_product',
        'replacement_qty', 'pickup_shop_id', 'photos', 'delivery_photo',
        'customer_confirm', 'confirm_reason', 'courier_rating', 'courier_rated_at',
        'problem_note', 'courier_sanction',
        'courier_deadline_at', 'assigned_at', 'en_route_at', 'delivered_at',
        'confirmed_at', 'admin_first_seen_at', 'admin_sla_notified_at',
        'courier_sla_warned_at', 'courier_sla_overdue_at', 'confirm_reminded_at',
        'photos_viewed_at', 'tg_courier_message_id', 'tg_mgmt_message_id', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'replacement_cost' => 'decimal:2',
            'address_lat' => 'float',
            'address_lng' => 'float',
            'courier_rating' => 'integer',
            'closed_at' => 'datetime',
            'courier_deadline_at' => 'datetime',
            'assigned_at' => 'datetime',
            'en_route_at' => 'datetime',
            'delivered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'courier_rated_at' => 'datetime',
            'admin_first_seen_at' => 'datetime',
            'admin_sla_notified_at' => 'datetime',
            'courier_sla_warned_at' => 'datetime',
            'courier_sla_overdue_at' => 'datetime',
            'confirm_reminded_at' => 'datetime',
            'photos_viewed_at' => 'datetime',
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

    public function pickupShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'pickup_shop_id');
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

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
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

    /** Roʻyxat: bitta chek / soʻrov qatori. */
    public function productSummary(): string
    {
        $this->loadMissing(['items', 'order']);
        $n = $this->items->count();
        $qty = (int) ($this->replacement_qty ?: $this->items->sum(fn (ClaimItem $i) => $i->brakQty()) ?: 1);
        if ($n <= 1) {
            return $this->productLabels()[0] ?? ('Brak ×'.$qty);
        }

        return $n.' mahsulot · '.$qty.' dona brak';
    }

    public function totalBrakQty(): int
    {
        $this->loadMissing('items');

        return (int) ($this->replacement_qty
            ?: $this->items->sum(fn (ClaimItem $i) => $i->brakQty())
            ?: 1);
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, self::CLOSED, true);
    }

    public function isCourierOpen(): bool
    {
        return in_array($this->status, ['assigned', 'en_route'], true);
    }

    public function daysOpen(): int
    {
        $end = $this->closed_at ?? now();

        return (int) $this->created_at->diffInDays($end);
    }
}
