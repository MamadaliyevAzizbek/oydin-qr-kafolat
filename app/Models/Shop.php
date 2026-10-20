<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = ['billz_id', 'name', 'code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function sellers(): HasMany
    {
        return $this->hasMany(Seller::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
