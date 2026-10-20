<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimStatusLog extends Model
{
    protected $fillable = ['claim_id', 'from_status', 'to_status', 'user_id', 'note'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
