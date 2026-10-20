<?php

namespace App\Models;

use App\Support\PanelPages;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'role_id',
        'shop_id',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function panelRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isAdmin(): bool
    {
        if ($this->panelRole?->isAdmin()) {
            return true;
        }

        return ($this->role ?? '') === 'admin';
    }

    /** Doʼkon menejeri — faqat oʼz doʼkoni (shop_id berilgan, admin emas). */
    public function forcedShopId(): ?int
    {
        if ($this->isAdmin() || ! $this->shop_id) {
            return null;
        }

        return (int) $this->shop_id;
    }

    /** @return list<string> */
    public function allowedPages(): array
    {
        if ($this->isAdmin()) {
            return PanelPages::ids();
        }

        $role = $this->panelRole;
        if (! $role) {
            return [];
        }

        return PanelPages::sanitize($role->pageIds());
    }

    public function canAccessPage(string $page): bool
    {
        return in_array($page, $this->allowedPages(), true);
    }
}
