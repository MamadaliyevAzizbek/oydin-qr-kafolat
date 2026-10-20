<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->string('claim_token', 64)->nullable()->unique()->after('telegram_id');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn('claim_token');
        });
    }
};
