<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            // Bir mahsulot qatori — bir marta kafolat (boshqa mijoz ura olmaydi)
            $table->unique('order_item_id', 'activations_order_item_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('activations', function (Blueprint $table) {
            $table->dropUnique('activations_order_item_id_unique');
        });
    }
};
