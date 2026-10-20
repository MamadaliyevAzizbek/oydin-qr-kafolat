<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('password'); // admin, manager, service, marketing
            $table->foreignId('shop_id')->nullable()->after('role');
        });

        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable()->unique();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable()->unique();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable()->unique();
            $table->string('sku')->nullable()->index();
            $table->string('name');
            $table->string('brand')->nullable()->index();
            $table->string('category')->nullable()->index();
            $table->string('supplier')->nullable()->index();
            $table->unsignedInteger('warranty_months')->default(12);
            $table->decimal('supply_price', 15, 2)->nullable();
            $table->decimal('retail_price', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('phone')->nullable()->index();
            $table->string('telegram_id')->nullable()->index();
            $table->boolean('marketing_consent')->default(true);
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('billz_id')->nullable()->unique();
            $table->string('order_number')->nullable()->index();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->nullable();
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamp('sold_at')->nullable()->index();
            $table->boolean('sync_error')->default(false);
            $table->text('sync_error_message')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('billz_product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->string('supplier')->nullable();
            $table->string('serial_number')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('supply_price', 15, 2)->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->unsignedInteger('warranty_months')->default(12);
            $table->timestamps();
        });

        Schema::create('qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('printed')->default(true);
            $table->timestamp('scanned_at')->nullable();
            $table->boolean('scanned_in_store')->default(false);
            $table->timestamps();
        });

        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->string('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable()->index();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('state')->nullable();
            $table->json('state_data')->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_token_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('warranty_ends_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('new'); // new, review, approved, rejected, replaced
            $table->string('reject_reason')->nullable();
            $table->decimal('replacement_cost', 15, 2)->nullable();
            $table->string('replacement_product')->nullable();
            $table->json('photos')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('claim_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bot_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('activation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stage'); // T1, T2, T3, T4
            $table->unsignedSmallInteger('days_after')->default(7);
            $table->string('status')->default('pending'); // pending, sent, delivered, answered, failed
            $table->string('answer')->nullable(); // good, problem
            $table->text('comment')->nullable();
            $table->string('telegram_message_id')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('alert'); // schedule, alert
            $table->boolean('enabled')->default(true);
            $table->string('schedule')->nullable(); // cron-like or daily/weekly/monthly
            $table->string('target')->default('management'); // management, service, purchase, admin
            $table->string('threshold')->nullable();
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // billz, telegram
            $table->string('job');
            $table->string('status'); // running, success, failed
            $table->unsignedInteger('records')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        // Fix shop_id FK on users after shops exist
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropColumn(['role', 'shop_id']);
        });

        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('notification_rules');
        Schema::dropIfExists('survey_messages');
        Schema::dropIfExists('claim_status_logs');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('activations');
        Schema::dropIfExists('bot_users');
        Schema::dropIfExists('qr_tokens');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('sellers');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('integration_settings');
    }
};
