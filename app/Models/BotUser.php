<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BotUser extends Model
{
    protected $fillable = [
        'telegram_id', 'claim_token', 'username', 'first_name', 'last_name', 'phone',
        'customer_id', 'state', 'state_data', 'is_blocked',
        'started_at', 'phone_verified_at', 'marketing_consent', 'consent_answered_at',
        'home_opens', 'last_home_at', 'last_seen_at',
    ];

    public function ensureClaimToken(): string
    {
        if ($this->claim_token) {
            return $this->claim_token;
        }

        do {
            $token = Str::lower(Str::random(40));
        } while (static::query()->where('claim_token', $token)->exists());

        $this->forceFill(['claim_token' => $token])->save();

        return $token;
    }

    protected function casts(): array
    {
        return [
            'state_data' => 'array',
            'is_blocked' => 'boolean',
            'marketing_consent' => 'boolean',
            'started_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'consent_answered_at' => 'datetime',
            'last_home_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
