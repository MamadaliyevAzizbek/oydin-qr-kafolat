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
    ];

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
        return $this->product?->name
            ?? $this->orderItem?->name
            ?? 'Mahsulot';
    }
}
