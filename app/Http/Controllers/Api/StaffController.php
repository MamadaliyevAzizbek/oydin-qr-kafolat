<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use App\Support\PanelPages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index()
    {
        return response()->json($this->payload());
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'pages' => 'nullable|array',
            'pages.*' => 'string',
        ]);

        $name = trim($data['name']);
        $slug = $this->uniqueSlug(Str::slug($name) ?: 'rol');

        $role = Role::query()->create([
            'name' => $name,
            'slug' => $slug,
            'pages' => PanelPages::sanitize($data['pages'] ?? []),
            'is_system' => false,
        ]);

        return response()->json(['ok' => true, 'role' => $this->roleRow($role), 'staff' => $this->payload()]);
    }

    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'pages' => 'nullable|array',
            'pages.*' => 'string',
        ]);

        $pages = PanelPages::sanitize($data['pages'] ?? []);
        if ($role->isAdmin()) {
            $pages = PanelPages::ids();
        }

        $role->update([
            'name' => trim($data['name']),
            'pages' => $pages,
        ]);

        return response()->json(['ok' => true, 'role' => $this->roleRow($role->fresh()), 'staff' => $this->payload()]);
    }

    public function destroyRole(Role $role)
    {
        if ($role->is_system || $role->isAdmin()) {
            return response()->json(['message' => 'Tizim rolini oʼchirib boʼlmaydi.'], 422);
        }
        if ($role->users()->exists()) {
            return response()->json(['message' => 'Bu rolga xodimlar biriktirilgan. Avval boshqa rolga oʼtkazing.'], 422);
        }

        $role->delete();

        return response()->json(['ok' => true, 'staff' => $this->payload()]);
    }

    public function storeEmployee(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'login' => 'required|string|max:120',
            'password' => 'required|string|min:6|max:120',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
            'shop_id' => 'nullable|exists:shops,id',
            'telegram_id' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
            'region' => 'nullable|string|max:120',
        ]);

        $login = trim($data['login']);
        [$username, $email] = $this->resolveLoginIdentity($login);

        if (User::query()->where('username', $username)->orWhere('email', $email)->exists()) {
            return response()->json(['message' => 'Bu login band.'], 422);
        }

        $role = Role::query()->findOrFail($data['role_id']);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'username' => $username,
            'email' => $email,
            'password' => $data['password'],
            'role_id' => $role->id,
            'role' => $role->slug,
            'shop_id' => $data['shop_id'] ?? null,
            'telegram_id' => $data['telegram_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'region' => $data['region'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        return response()->json(['ok' => true, 'employee' => $this->employeeRow($user->load('panelRole')), 'staff' => $this->payload()]);
    }

    public function updateEmployee(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'login' => 'required|string|max:120',
            'password' => 'nullable|string|min:6|max:120',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'sometimes|boolean',
            'shop_id' => 'nullable|exists:shops,id',
            'telegram_id' => 'nullable|string|max:64',
            'phone' => 'nullable|string|max:32',
            'region' => 'nullable|string|max:120',
        ]);

        $login = trim($data['login']);
        [$username, $email] = $this->resolveLoginIdentity($login);

        $taken = User::query()
            ->where('id', '!=', $user->id)
            ->where(function ($q) use ($username, $email) {
                $q->where('username', $username)->orWhere('email', $email);
            })
            ->exists();
        if ($taken) {
            return response()->json(['message' => 'Bu login band.'], 422);
        }

        $role = Role::query()->findOrFail($data['role_id']);

        // Oʼzini admin emas rolga tushirib, xodimlar boʼlimidan mahrum qilmaslik
        if ((int) $user->id === (int) auth()->id() && ! $role->isAdmin() && ! in_array('xodimlar', PanelPages::sanitize($role->pageIds()), true)) {
            return response()->json(['message' => 'Oʼzingizni Xodimlar boʼlimidan mahrum qila olmaysiz.'], 422);
        }

        $payload = [
            'name' => trim($data['name']),
            'username' => $username,
            'email' => $email,
            'role_id' => $role->id,
            'role' => $role->slug,
            'shop_id' => $data['shop_id'] ?? null,
            'telegram_id' => $data['telegram_id'] ?? null,
            'phone' => $data['phone'] ?? null,
            'region' => $data['region'] ?? null,
        ];
        if (array_key_exists('is_active', $data)) {
            if ((int) $user->id === (int) auth()->id() && ! $data['is_active']) {
                return response()->json(['message' => 'Oʼzingizni oʼchira olmaysiz.'], 422);
            }
            $payload['is_active'] = (bool) $data['is_active'];
        }
        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return response()->json(['ok' => true, 'employee' => $this->employeeRow($user->fresh()->load('panelRole')), 'staff' => $this->payload()]);
    }

    public function destroyEmployee(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return response()->json(['message' => 'Oʼzingizni oʼchira olmaysiz.'], 422);
        }
        if ($user->isAdmin() && User::query()->whereHas('panelRole', fn ($q) => $q->where('slug', 'admin'))->count() <= 1) {
            return response()->json(['message' => 'Oxirgi adminni oʼchirib boʼlmaydi.'], 422);
        }

        $user->delete();

        return response()->json(['ok' => true, 'staff' => $this->payload()]);
    }

    /** @return array<string, mixed> */
    protected function payload(): array
    {
        $roles = Role::query()->withCount('users')->orderBy('id')->get()->map(fn (Role $r) => $this->roleRow($r));
        $employees = User::query()->with(['panelRole', 'shop'])->orderBy('name')->get()->map(fn (User $u) => $this->employeeRow($u));

        return [
            'pages' => PanelPages::all(),
            'roles' => $roles,
            'employees' => $employees,
            'shops' => Shop::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])
                ->map(fn (Shop $s) => ['id' => $s->id, 'name' => $s->name])->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    protected function roleRow(Role $r): array
    {
        $pages = $r->isAdmin() ? PanelPages::ids() : $r->pageIds();

        return [
            'id' => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
            'pages' => $pages,
            'is_system' => (bool) $r->is_system,
            'is_admin' => $r->isAdmin(),
            'users_count' => (int) ($r->users_count ?? $r->users()->count()),
        ];
    }

    /** @return array<string, mixed> */
    protected function employeeRow(User $u): array
    {
        return [
            'id' => $u->id,
            'name' => $u->name,
            'login' => $u->username ?: $u->email,
            'email' => $u->email,
            'role_id' => $u->role_id,
            'role_name' => $u->panelRole?->name ?? ($u->role ?: '—'),
            'is_active' => (bool) $u->is_active,
            'shop_id' => $u->shop_id,
            'shop_name' => $u->shop?->name,
            'telegram_id' => $u->telegram_id,
            'phone' => $u->phone,
            'region' => $u->region,
            'is_self' => (int) $u->id === (int) auth()->id(),
        ];
    }

    /** @return array{0:string,1:string} */
    protected function resolveLoginIdentity(string $login): array
    {
        $login = trim($login);
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $username = Str::before($login, '@');

            return [$username, Str::lower($login)];
        }

        $username = Str::lower(preg_replace('/\s+/', '', $login) ?: 'user');

        return [$username, $username.'@staff.oydin.local'];
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Role::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
