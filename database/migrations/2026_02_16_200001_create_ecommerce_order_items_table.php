<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ecommerce_order_id')->unsigned();
            $table->integer('product_id')->unsigned()->nullable();
            $table->string('source_product_id')->nullable();
            $table->string('title');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 16, 2)->default(0);
            $table->decimal('line_total', 16, 2)->default(0);
            $table->timestamps();

            $table->foreign('ecommerce_order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecommerce_order_items');
    }
}