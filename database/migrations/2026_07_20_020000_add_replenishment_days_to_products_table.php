<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'replenishment_days')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('replenishment_days')->nullable()->default(null)->after('stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'replenishment_days')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('replenishment_days');
            });
        }
    }
};
