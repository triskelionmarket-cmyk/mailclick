<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid');
            $table->integer('source_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('subscriber_id')->unsigned()->nullable();
            $table->string('source_order_id');
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('total', 16, 2)->default(0);
            $table->string('currency', 10)->default('RON');
            $table->longText('meta')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->unique(['source_id', 'source_order_id']);
            $table->index('email');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecommerce_orders');
    }
}