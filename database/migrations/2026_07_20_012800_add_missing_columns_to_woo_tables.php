<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing columns to WooCommerce tables that are used by seed data and analytics UI.
     */
    public function up(): void
    {
        // woo_customers: add username, city, country
        Schema::table('woo_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('woo_customers', 'username')) {
                $table->string('username')->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('woo_customers', 'city')) {
                $table->string('city')->nullable()->after('username');
            }
            if (!Schema::hasColumn('woo_customers', 'country')) {
                $table->string('country')->default('RO')->after('city');
            }
        });

        // woo_orders: add order_number, currency, woo_customer_id, billing fields
        Schema::table('woo_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('woo_orders', 'order_number')) {
                $table->string('order_number')->nullable()->after('woo_order_id');
            }
            if (!Schema::hasColumn('woo_orders', 'woo_customer_id')) {
                $table->unsignedBigInteger('woo_customer_id')->default(0)->after('customer_id');
            }
            if (!Schema::hasColumn('woo_orders', 'currency')) {
                $table->string('currency', 10)->default('RON')->after('total');
            }
            if (!Schema::hasColumn('woo_orders', 'billing_email')) {
                $table->string('billing_email')->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('woo_orders', 'billing_first_name')) {
                $table->string('billing_first_name')->nullable()->after('billing_email');
            }
            if (!Schema::hasColumn('woo_orders', 'billing_last_name')) {
                $table->string('billing_last_name')->nullable()->after('billing_first_name');
            }
        });

        // woo_products: add stock_status
        Schema::table('woo_products', function (Blueprint $table) {
            if (!Schema::hasColumn('woo_products', 'stock_status')) {
                $table->string('stock_status')->default('instock')->after('stock_quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('woo_customers', function (Blueprint $table) {
            $table->dropColumn(['username', 'city', 'country']);
        });

        Schema::table('woo_orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'woo_customer_id', 'currency', 'billing_email', 'billing_first_name', 'billing_last_name']);
        });

        Schema::table('woo_products', function (Blueprint $table) {
            $table->dropColumn(['stock_status']);
        });
    }
};
