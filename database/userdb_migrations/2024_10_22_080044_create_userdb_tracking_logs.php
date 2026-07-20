<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbTrackingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('status');
            $table->string('runtime_message_id')->nullable();
            $table->string('message_id')->nullable();
            $table->text('error')->nullable();

            $table->bigInteger('sending_server_id')->unsigned(); // master db

            $table->bigInteger('subscriber_id')->unsigned();
            $table->bigInteger('campaign_id')->unsigned()->nullable();
            $table->bigInteger('email_id')->unsigned()->nullable();
            $table->bigInteger('auto_trigger_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->foreign('auto_trigger_id')->references('id')->on('auto_triggers')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            // INDEX
            $table->unique('message_id');
            $table->unique('runtime_message_id');
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
        Schema::dropIfExists('tracking_logs');
    }
}
