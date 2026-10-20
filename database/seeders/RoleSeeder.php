<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Support\PanelPages;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'pages' => PanelPages::ids(),
                'is_system' => true,
            ]
        );

        User::query()->where('email', 'service@oydin.uz')->delete();

        // Faqat admin qoladi — boshqa rollarni panelda oʼzingiz yaratasiz
        Role::query()->where('slug', '!=', 'admin')->delete();

        User::query()->where('email', 'admin@oydin.uz')->update([
            'role_id' => $admin->id,
            'role' => 'admin',
            'username' => 'admin',
            'is_active' => true,
        ]);
    }
}
