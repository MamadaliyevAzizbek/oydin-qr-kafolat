<?php

namespace Database\Seeders;

use App\Models\NotificationRule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Production / toza muhit: faqat rollar, admin, xabarnoma qoidalari.
 * Demo do‘kon/chek/brak yo‘q.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRoleId = \App\Models\Role::query()->where('slug', 'admin')->value('id');

        User::query()->updateOrCreate(
            ['email' => 'admin@oydin.uz'],
            [
                'name' => 'admin',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'role_id' => $adminRoleId,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::query()->where('email', 'service@oydin.uz')->delete();

        $rules = [
            ['daily_report', 'Kunlik hisobot', 'schedule', 'daily_09:00', 'management', null],
            ['weekly_report', 'Haftalik hisobot', 'schedule', 'monday_09:00', 'management', null],
            ['monthly_report', 'Oylik hisobot PDF', 'schedule', 'month_1', 'management', null],
            ['new_claim', 'Yangi brak so\'rovi', 'alert', null, 'service', null],
            ['claim_sla', 'So\'rov 7 kundan oshdi', 'alert', null, 'service', '7'],
            ['product_brak_3pct', 'Mahsulot brak 3%+', 'alert', null, 'management', '3'],
            ['billz_sync_down', 'Billz sinxron to\'xtadi', 'alert', null, 'management', '30'],
        ];
        foreach ($rules as $r) {
            NotificationRule::query()->updateOrCreate(
                ['code' => $r[0]],
                [
                    'name' => $r[1],
                    'type' => $r[2],
                    'enabled' => true,
                    'schedule' => $r[3],
                    'target' => $r[4],
                    'threshold' => $r[5],
                ]
            );
        }
    }
}
