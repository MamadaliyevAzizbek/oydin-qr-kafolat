<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->boolean('marketing_consent')->nullable()->after('phone_verified_at');
            $table->timestamp('consent_answered_at')->nullable()->after('marketing_consent');
            $table->unsignedInteger('home_opens')->default(0)->after('consent_answered_at');
            $table->timestamp('last_home_at')->nullable()->after('home_opens');
        });
    }

    public function down(): void
    {
        Schema::table('bot_users', function (Blueprint $table) {
            $table->dropColumn(['marketing_consent', 'consent_answered_at', 'home_opens', 'last_home_at']);
        });
    }
};
