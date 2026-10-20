<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activation extends Model
{
    protected $fillable = [
        'qr_token_id', 'order_id', 'order_item_id', 'customer_id',
        'bot_user_id', 'activated_at', 'warranty_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'warranty_ends_at' => 'datetime',
        ];
    }

    public function qrToken(): BelongsTo
    {
        return $this->belongsTo(QrToken::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function surveys(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SurveyMessage::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }
}
