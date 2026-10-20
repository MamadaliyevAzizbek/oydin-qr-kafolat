<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('claim_items', function (Blueprint $table) {
            if (! Schema::hasColumn('claim_items', 'reason')) {
                $table->string('reason', 80)->nullable()->after('qty');
            }
            if (! Schema::hasColumn('claim_items', 'description')) {
                $table->text('description')->nullable()->after('reason');
            }
            if (! Schema::hasColumn('claim_items', 'photos')) {
                $table->json('photos')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('claim_items', function (Blueprint $table) {
            foreach (['photos', 'description', 'reason'] as $col) {
                if (Schema::hasColumn('claim_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
