<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'billz_id', 'order_number', 'shop_id', 'seller_id', 'customer_id',
        'status', 'total_price', 'sold_at', 'sync_error', 'sync_error_message', 'raw',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'sold_at' => 'datetime',
            'sync_error' => 'boolean',
            'raw' => 'array',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function qrTokens(): HasMany
    {
        return $this->hasMany(QrToken::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(SurveyMessage::class);
    }
}
