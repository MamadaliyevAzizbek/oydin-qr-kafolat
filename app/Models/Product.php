<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'billz_id', 'sku', 'name', 'brand', 'category', 'supplier',
        'warranty_months', 'supply_price', 'retail_price',
    ];

    protected function casts(): array
    {
        return [
            'supply_price' => 'decimal:2',
            'retail_price' => 'decimal:2',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
