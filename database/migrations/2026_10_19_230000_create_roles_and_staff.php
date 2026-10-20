<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('pages')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
            $table->string('username')->nullable()->unique()->after('email');
            $table->boolean('is_active')->default(true)->after('shop_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['username', 'is_active']);
        });
        Schema::dropIfExists('roles');
    }
};
