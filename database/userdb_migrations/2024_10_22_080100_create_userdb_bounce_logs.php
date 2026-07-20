<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbBounceLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bounce_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('runtime_message_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('bounce_type');
            $table->text('raw');
            $table->string('status_code')->nullable();

            $table->bigInteger('tracking_log_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullalbe();

            $table->foreign('tracking_log_id')->references('id')->nullable()->on('tracking_logs')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->nullable()->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bounce_logs');
    }
}
