<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyMessage extends Model
{
    protected $fillable = [
        'customer_id', 'bot_user_id', 'order_id', 'activation_id',
        'stage', 'days_after', 'status', 'answer', 'comment',
        'telegram_message_id', 'scheduled_at', 'sent_at',
        'delivered_at', 'answered_at', 'error_message',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'answered_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }
}
