<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimItem extends Model
{
    protected $fillable = [
        'claim_id',
        'activation_id',
        'order_id',
        'order_item_id',
        'product_id',
        'qty',
        'reason',
        'description',
        'photos',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'photos' => 'array',
        ];
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function displayName(): string
    {
        $name = $this->product?->name
            ?? $this->orderItem?->name
            ?? 'Mahsulot';
        $brak = max(1, (int) ($this->qty ?: 1));

        return "{$name} ×{$brak}";
    }

    /** Hisobotlar uchun brak dona. */
    public function brakQty(): int
    {
        return max(1, (int) ($this->qty ?: 1));
    }
}
