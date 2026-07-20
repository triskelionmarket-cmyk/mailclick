<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbAutoTriggers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_triggers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->text('data')->nullable();
            $table->text('executed_index')->nullable();
            $table->text('cached_subscriber')->nullable();
            $table->boolean('is_error')->default(false);

            $table->bigInteger('subscriber_id')->unsigned()->nullable();  // in case contact is moved to another list, then this field value is NULL
            $table->bigInteger('automation2_id')->unsigned();
            $table->bigInteger('trigger_session_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
            $table->foreign('trigger_session_id')->references('id')->on('trigger_sessions')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auto_triggers');
    }
}
