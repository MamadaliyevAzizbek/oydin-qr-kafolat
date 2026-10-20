<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = [
        'source', 'job', 'status', 'records', 'message', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
