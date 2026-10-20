<?php

namespace Database\Seeders;

use App\Models\Activation;
use App\Models\BotUser;
use App\Models\Claim;
use App\Models\ClaimStatusLog;
use App\Models\Customer;
use App\Models\NotificationRule;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\QrToken;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\SurveyMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $adminRoleId = \App\Models\Role::query()->where('slug', 'admin')->value('id');

        $admin = User::query()->updateOrCreate(
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

        $shopNames = ['Chilonzor', 'Yunusobod', 'Sergeli', 'Olmazor', 'Yakkasaroy', "Mirzo Ulug'bek"];
        $shops = collect($shopNames)->map(fn ($name, $i) => Shop::query()->updateOrCreate(
            ['code' => 's'.$i],
            ['name' => $name, 'billz_id' => 'shop-demo-'.$i, 'is_active' => true]
        ));

        $sellers = collect();
        foreach ($shops as $shop) {
            foreach (range(1, 2) as $n) {
                $sellers->push(Seller::query()->updateOrCreate(
                    ['billz_id' => "seller-{$shop->id}-{$n}"],
                    ['shop_id' => $shop->id, 'name' => "Sotuvchi {$shop->name} {$n}", 'is_active' => true]
                ));
            }
        }

        $products = collect([
            ['LED lampa 12W E27', 'Lumex', 'Yoritish', 'Orient'],
            ['LED panel 36W', 'Lumex', 'Yoritish', 'Orient'],
            ['Avtomat 16A C', 'Elektra', 'Avtomatlar', 'ElectroTrade'],
            ['Stabilizator 5 kVt', 'Volter', 'Stabilizatorlar', 'Volter UZ'],
            ['Kabel VVG 3×2,5', 'Uzkabel', 'Kabel', 'Uzkabel'],
            ['Rozetka ichki', 'Vesta', 'Rozetkalar', 'Vesta'],
        ])->map(fn ($p, $i) => Product::query()->updateOrCreate(
            ['sku' => 'SKU-'.$i],
            [
                'billz_id' => 'prod-demo-'.$i,
                'name' => $p[0],
                'brand' => $p[1],
                'category' => $p[2],
                'supplier' => $p[3],
                'warranty_months' => 12,
                'retail_price' => 50000 + $i * 10000,
                'supply_price' => 30000 + $i * 5000,
            ]
        ));

        foreach (range(1, 40) as $i) {
            $shop = $shops->random();
            $seller = $sellers->where('shop_id', $shop->id)->random();
            $customer = Customer::query()->create([
                'billz_id' => 'cust-demo-'.$i,
                'name' => 'Mijoz '.$i,
                'phone' => '+99890'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                'marketing_consent' => true,
                'registered_at' => now()->subDays(rand(1, 60)),
            ]);

            $soldAt = now()->subDays(rand(0, 29))->setTime(rand(9, 18), rand(0, 59));
            $order = Order::query()->create([
                'billz_id' => 'order-demo-'.$i,
                'order_number' => 'B-'.(48000 + $i),
                'shop_id' => $shop->id,
                'seller_id' => $seller->id,
                'customer_id' => $customer->id,
                'status' => 'completed',
                'total_price' => 0,
                'sold_at' => $soldAt,
            ]);

            $product = $products->random();
            $item = OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'billz_product_id' => $product->billz_id,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->brand,
                'category' => $product->category,
                'supplier' => $product->supplier,
                'serial_number' => rand(0, 1) ? 'SN-'.$i : null,
                'price' => $product->retail_price,
                'supply_price' => $product->supply_price,
                'quantity' => 1,
                'warranty_months' => 12,
            ]);
            $order->update(['total_price' => $item->price]);

            $qr = QrToken::query()->create([
                'token' => Str::lower(Str::random(16)),
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'printed' => true,
                'scanned_at' => rand(0, 100) < 65 ? $soldAt->copy()->addHours(2) : null,
                'scanned_in_store' => rand(0, 100) < 35,
            ]);

            if ($qr->scanned_at && rand(0, 100) < 85) {
                $bot = BotUser::query()->create([
                    'telegram_id' => (string) (900000000 + $i),
                    'first_name' => 'TG'.$i,
                    'phone' => $customer->phone,
                    'customer_id' => $customer->id,
                    'started_at' => $qr->scanned_at,
                    'phone_verified_at' => $qr->scanned_at->copy()->addMinutes(5),
                    'last_seen_at' => now()->subDays(rand(0, 10)),
                ]);
                $customer->update(['telegram_id' => $bot->telegram_id]);
                $activation = Activation::query()->create([
                    'qr_token_id' => $qr->id,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'customer_id' => $customer->id,
                    'bot_user_id' => $bot->id,
                    'activated_at' => $bot->phone_verified_at,
                    'warranty_ends_at' => $bot->phone_verified_at->copy()->addMonths(12),
                ]);

                if (rand(0, 100) < 40) {
                    SurveyMessage::query()->create([
                        'customer_id' => $customer->id,
                        'bot_user_id' => $bot->id,
                        'order_id' => $order->id,
                        'activation_id' => $activation->id,
                        'stage' => 'T1',
                        'days_after' => 7,
                        'status' => 'answered',
                        'answer' => rand(0, 100) < 85 ? 'good' : 'problem',
                        'sent_at' => $activation->activated_at->copy()->addDays(7),
                        'delivered_at' => $activation->activated_at->copy()->addDays(7),
                        'answered_at' => $activation->activated_at->copy()->addDays(7)->addHours(2),
                    ]);
                }
            }
        }

        $statuses = ['new', 'review', 'approved', 'assigned', 'done', 'rejected'];
        foreach (range(1, 12) as $i) {
            $order = Order::query()->inRandomOrder()->first();
            $status = $statuses[array_rand($statuses)];
            $claim = Claim::query()->create([
                'number' => 'QK-'.(1050 - $i),
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'order_item_id' => $order->items()->first()?->id,
                'product_id' => $order->items()->first()?->product_id,
                'shop_id' => $order->shop_id,
                'seller_id' => $order->seller_id,
                'assignee_id' => in_array($status, ['new'], true) ? null : $admin->id,
                'reason' => 'Ishlamaydi',
                'description' => 'Demo brak so\'rovi #'.$i,
                'status' => $status,
                'reject_reason' => $status === 'rejected' ? 'Mexanik shikast' : null,
                'replacement_cost' => $status === 'done' ? 120000 : null,
                'closed_at' => in_array($status, ['rejected', 'done', 'unconfirmed'], true) ? now()->subDays(rand(0, 5)) : null,
                'created_at' => now()->subDays(rand(0, 20)),
            ]);
            ClaimStatusLog::query()->create([
                'claim_id' => $claim->id,
                'from_status' => null,
                'to_status' => 'new',
                'user_id' => $admin->id,
                'note' => 'Yaratildi',
            ]);
            if ($status !== 'new') {
                ClaimStatusLog::query()->create([
                    'claim_id' => $claim->id,
                    'from_status' => 'new',
                    'to_status' => $status,
                    'user_id' => $admin->id,
                ]);
            }
        }

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
