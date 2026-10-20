<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'enabled', 'schedule', 'target', 'threshold',
    ];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }
}
