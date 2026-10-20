<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'billz_id', 'name', 'phone', 'telegram_id',
        'marketing_consent', 'registered_at',
    ];

    protected function casts(): array
    {
        return [
            'marketing_consent' => 'boolean',
            'registered_at' => 'datetime',
        ];
    }

    /** Faqat Telegram bot orqali roʼyxatdan oʼtgan mijozlar. */
    public function scopeFromBot(Builder $query): Builder
    {
        return $query->whereHas('botUsers');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function surveys(): HasMany
    {
        return $this->hasMany(SurveyMessage::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }

    public function botUsers(): HasMany
    {
        return $this->hasMany(BotUser::class);
    }
}
