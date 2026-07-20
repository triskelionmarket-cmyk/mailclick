<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for WooCommerce analytics and sync engine.
     */
    public function up(): void
    {
        // 1. Woo Stores
        if (!Schema::hasTable('woo_stores')) {
            Schema::create('woo_stores', function (Blueprint $table) {
                $table->id();
                $table->string('uid', 36)->unique();
                $table->unsignedInteger('customer_id');
                $table->string('store_url');
                $table->string('store_name')->nullable();
                $table->string('api_token', 100)->nullable();
                $table->string('consumer_key')->nullable();
                $table->string('consumer_secret')->nullable();
                $table->string('webhook_secret')->nullable();
                $table->string('sync_status')->default('idle'); // idle, syncing, completed, failed
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            });
        }

        // 2. Woo Categories
        if (!Schema::hasTable('woo_categories')) {
            Schema::create('woo_categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('woo_category_id');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
                $table->unique(['store_id', 'woo_category_id']);
            });
        }

        // 3. Woo Products
        if (!Schema::hasTable('woo_products')) {
            Schema::create('woo_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('woo_product_id');
                $table->string('name');
                $table->string('sku')->nullable();
                $table->decimal('price', 12, 2)->default(0.00);
                $table->decimal('regular_price', 12, 2)->default(0.00);
                $table->decimal('sale_price', 12, 2)->nullable();
                $table->decimal('purchase_cost', 12, 2)->default(0.00)->comment('Cost de achiziție setat în MailClick');
                $table->integer('stock_quantity')->default(0);
                $table->json('categories_json')->nullable();
                $table->json('images_json')->nullable();
                $table->decimal('rfm_score', 5, 2)->default(0.00);
                $table->decimal('conversion_rate', 5, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
                $table->unique(['store_id', 'woo_product_id']);
            });
        }

        // 4. Woo Customers
        if (!Schema::hasTable('woo_customers')) {
            Schema::create('woo_customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('woo_customer_id')->default(0);
                $table->string('email')->index();
                $table->string('phone')->nullable();
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->decimal('total_spent', 12, 2)->default(0.00);
                $table->integer('orders_count')->default(0);
                $table->integer('rfm_recency')->default(0)->comment('Zile de la ultima comandă');
                $table->integer('rfm_frequency')->default(0);
                $table->decimal('rfm_monetary', 12, 2)->default(0.00);
                $table->decimal('rfm_score', 5, 2)->default(0.00);
                $table->decimal('clv_estimated', 12, 2)->default(0.00);
                $table->timestamp('last_order_at')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
                $table->unique(['store_id', 'email']);
            });
        }

        // 5. Woo Orders
        if (!Schema::hasTable('woo_orders')) {
            Schema::create('woo_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('woo_order_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_email')->index();
                $table->string('customer_phone')->nullable();
                $table->decimal('total', 12, 2)->default(0.00);
                $table->string('status')->default('completed');
                $table->string('payment_method')->nullable();
                $table->integer('items_count')->default(0);
                $table->timestamp('date_created')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
                $table->unique(['store_id', 'woo_order_id']);
            });
        }

        // 6. Woo Order Items
        if (!Schema::hasTable('woo_order_items')) {
            Schema::create('woo_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('woo_product_id');
                $table->string('name');
                $table->integer('qty')->default(1);
                $table->decimal('price', 12, 2)->default(0.00);
                $table->decimal('total', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
                $table->foreign('order_id')->references('id')->on('woo_orders')->onDelete('cascade');
            });
        }

        // 7. Woo Reviews
        if (!Schema::hasTable('woo_reviews')) {
            Schema::create('woo_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('woo_review_id')->default(0);
                $table->unsignedBigInteger('woo_product_id');
                $table->integer('rating')->default(5);
                $table->text('review_text')->nullable();
                $table->string('reviewer_name')->nullable();
                $table->string('reviewer_email')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('woo_stores')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_reviews');
        Schema::dropIfExists('woo_order_items');
        Schema::dropIfExists('woo_orders');
        Schema::dropIfExists('woo_customers');
        Schema::dropIfExists('woo_products');
        Schema::dropIfExists('woo_categories');
        Schema::dropIfExists('woo_stores');
    }
};
