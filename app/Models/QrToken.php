<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QrToken extends Model
{
    protected $fillable = [
        'token', 'order_id', 'order_item_id', 'printed',
        'scanned_at', 'scanned_in_store',
    ];

    protected function casts(): array
    {
        return [
            'printed' => 'boolean',
            'scanned_at' => 'datetime',
            'scanned_in_store' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function activation(): HasOne
    {
        return $this->hasOne(Activation::class);
    }
}
