<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbOpenLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('message_id');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->bigInteger('tracking_log_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullalbe();

            $table->foreign('tracking_log_id')->references('id')->on('tracking_logs')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('message_id')->references('message_id')->on('tracking_logs')->onDelete('cascade');

            // index
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('open_logs');
    }
}
