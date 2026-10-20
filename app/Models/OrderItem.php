<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'billz_product_id', 'name', 'sku', 'brand',
        'category', 'supplier', 'serial_number', 'price', 'supply_price',
        'quantity', 'warranty_months',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'supply_price' => 'decimal:2',
            'quantity' => 'decimal:3',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
