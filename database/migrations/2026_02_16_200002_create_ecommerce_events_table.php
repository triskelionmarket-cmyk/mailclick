<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEcommerceEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id')->unsigned();
            $table->integer('subscriber_id')->unsigned()->nullable();
            $table->string('email')->nullable();
            $table->string('event_type'); // page_view, product_view, add_to_cart, begin_checkout, purchase
            $table->string('page_url')->nullable();
            $table->string('source_product_id')->nullable();
            $table->string('product_title')->nullable();
            $table->decimal('value', 16, 2)->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('sources')->onDelete('cascade');
            $table->index('email');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecommerce_events');
    }
}