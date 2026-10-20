<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            if (! Schema::hasColumn('claims', 'courier_rating')) {
                $table->unsignedTinyInteger('courier_rating')->nullable()->after('customer_confirm');
            }
            if (! Schema::hasColumn('claims', 'courier_rated_at')) {
                $table->timestamp('courier_rated_at')->nullable()->after('courier_rating');
            }
        });
    }

    public function down(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $drop = [];
            foreach (['courier_rating', 'courier_rated_at'] as $col) {
                if (Schema::hasColumn('claims', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
