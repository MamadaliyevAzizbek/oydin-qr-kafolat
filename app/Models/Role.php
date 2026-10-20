<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'pages',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'pages' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return list<string> */
    public function pageIds(): array
    {
        $pages = $this->pages;
        if (! is_array($pages)) {
            return [];
        }

        return array_values(array_unique(array_map('strval', $pages)));
    }

    public function isAdmin(): bool
    {
        return $this->slug === 'admin';
    }
}
