<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telegram_id')) {
                $table->string('telegram_id')->nullable()->unique()->after('shop_id');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 32)->nullable()->after('telegram_id');
            }
            if (! Schema::hasColumn('users', 'region')) {
                $table->string('region', 120)->nullable()->after('phone');
            }
        });

        Schema::table('claims', function (Blueprint $table) {
            if (! Schema::hasColumn('claims', 'address')) {
                $table->text('address')->nullable()->after('description');
            }
            if (! Schema::hasColumn('claims', 'address_lat')) {
                $table->decimal('address_lat', 10, 7)->nullable()->after('address');
            }
            if (! Schema::hasColumn('claims', 'address_lng')) {
                $table->decimal('address_lng', 10, 7)->nullable()->after('address_lat');
            }
            if (! Schema::hasColumn('claims', 'courier_id')) {
                $table->foreignId('courier_id')->nullable()->after('assignee_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('claims', 'courier_deadline_at')) {
                $table->timestamp('courier_deadline_at')->nullable()->after('courier_id');
            }
            if (! Schema::hasColumn('claims', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('courier_deadline_at');
            }
            if (! Schema::hasColumn('claims', 'en_route_at')) {
                $table->timestamp('en_route_at')->nullable()->after('assigned_at');
            }
            if (! Schema::hasColumn('claims', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('en_route_at');
            }
            if (! Schema::hasColumn('claims', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('delivered_at');
            }
            if (! Schema::hasColumn('claims', 'delivery_photo')) {
                $table->string('delivery_photo')->nullable()->after('photos');
            }
            if (! Schema::hasColumn('claims', 'customer_confirm')) {
                $table->string('customer_confirm', 16)->nullable()->after('delivery_photo');
            }
            if (! Schema::hasColumn('claims', 'confirm_reason')) {
                $table->string('confirm_reason')->nullable()->after('customer_confirm');
            }
            if (! Schema::hasColumn('claims', 'replacement_qty')) {
                $table->unsignedSmallInteger('replacement_qty')->nullable()->after('replacement_product');
            }
            if (! Schema::hasColumn('claims', 'pickup_shop_id')) {
                $table->foreignId('pickup_shop_id')->nullable()->after('replacement_qty')->constrained('shops')->nullOnDelete();
            }
            if (! Schema::hasColumn('claims', 'problem_note')) {
                $table->text('problem_note')->nullable()->after('pickup_shop_id');
            }
            if (! Schema::hasColumn('claims', 'courier_sanction')) {
                $table->unsignedTinyInteger('courier_sanction')->default(0)->after('problem_note');
            }
            if (! Schema::hasColumn('claims', 'admin_first_seen_at')) {
                $table->timestamp('admin_first_seen_at')->nullable()->after('courier_sanction');
            }
            if (! Schema::hasColumn('claims', 'admin_sla_notified_at')) {
                $table->timestamp('admin_sla_notified_at')->nullable()->after('admin_first_seen_at');
            }
            if (! Schema::hasColumn('claims', 'courier_sla_warned_at')) {
                $table->timestamp('courier_sla_warned_at')->nullable()->after('admin_sla_notified_at');
            }
            if (! Schema::hasColumn('claims', 'courier_sla_overdue_at')) {
                $table->timestamp('courier_sla_overdue_at')->nullable()->after('courier_sla_warned_at');
            }
            if (! Schema::hasColumn('claims', 'confirm_reminded_at')) {
                $table->timestamp('confirm_reminded_at')->nullable()->after('courier_sla_overdue_at');
            }
            if (! Schema::hasColumn('claims', 'photos_viewed_at')) {
                $table->timestamp('photos_viewed_at')->nullable()->after('confirm_reminded_at');
            }
            if (! Schema::hasColumn('claims', 'tg_courier_message_id')) {
                $table->string('tg_courier_message_id', 64)->nullable()->after('photos_viewed_at');
            }
            if (! Schema::hasColumn('claims', 'tg_mgmt_message_id')) {
                $table->string('tg_mgmt_message_id', 64)->nullable()->after('tg_courier_message_id');
            }
        });

        if (! Schema::hasColumn('claim_status_logs', 'actor')) {
            Schema::table('claim_status_logs', function (Blueprint $table) {
                $table->string('actor', 32)->nullable()->after('user_id');
                $table->string('source', 32)->nullable()->after('actor');
            });
        }

        // Eski → yangi statuslar
        DB::table('claims')->where('status', 'replaced')->update(['status' => 'done']);

        if (Schema::hasTable('roles')) {
            $now = now();
            if (! DB::table('roles')->where('slug', 'kuryer')->exists()) {
                DB::table('roles')->insert([
                    'name' => 'Kuryer',
                    'slug' => 'kuryer',
                    'pages' => json_encode(['kuryer']),
                    'is_system' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $admin = DB::table('roles')->where('slug', 'admin')->first();
            if ($admin) {
                $pages = json_decode($admin->pages ?? '[]', true) ?: [];
                foreach (['kuryer', 'miniapp'] as $p) {
                    if (! in_array($p, $pages, true)) {
                        $pages[] = $p;
                    }
                }
                DB::table('roles')->where('id', $admin->id)->update([
                    'pages' => json_encode(array_values($pages)),
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ([
            'courier_sla_hours' => '72',
            'admin_sla_hours' => '2',
            'admin_sla_repeat_hours' => '2',
            'confirm_reminder_hours' => '24',
            'confirm_autoclose_hours' => '72',
        ] as $key => $val) {
            if (! DB::table('integration_settings')->where('key', $key)->exists()) {
                DB::table('integration_settings')->insert([
                    'key' => $key,
                    'value' => $val,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('claims')->where('status', 'done')->update(['status' => 'replaced']);
        DB::table('claims')->whereIn('status', ['assigned', 'en_route', 'delivered', 'dispute', 'unconfirmed'])
            ->update(['status' => 'approved']);

        if (Schema::hasColumn('claims', 'courier_id')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->dropConstrainedForeignId('courier_id');
            });
        }
        if (Schema::hasColumn('claims', 'pickup_shop_id')) {
            Schema::table('claims', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pickup_shop_id');
            });
        }

        Schema::table('claims', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'address', 'address_lat', 'address_lng', 'courier_deadline_at',
                'assigned_at', 'en_route_at', 'delivered_at', 'confirmed_at',
                'delivery_photo', 'customer_confirm', 'confirm_reason',
                'replacement_qty', 'problem_note', 'courier_sanction',
                'admin_first_seen_at', 'admin_sla_notified_at',
                'courier_sla_warned_at', 'courier_sla_overdue_at', 'confirm_reminded_at',
                'photos_viewed_at', 'tg_courier_message_id', 'tg_mgmt_message_id',
            ] as $col) {
                if (Schema::hasColumn('claims', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });

        if (Schema::hasColumn('claim_status_logs', 'actor')) {
            Schema::table('claim_status_logs', function (Blueprint $table) {
                $cols = ['actor'];
                if (Schema::hasColumn('claim_status_logs', 'source')) {
                    $cols[] = 'source';
                }
                $table->dropColumn($cols);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['telegram_id', 'phone', 'region'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        DB::table('roles')->where('slug', 'kuryer')->delete();
        DB::table('integration_settings')->whereIn('key', [
            'courier_sla_hours', 'admin_sla_hours', 'admin_sla_repeat_hours',
            'confirm_reminder_hours', 'confirm_autoclose_hours',
        ])->delete();
    }
};
