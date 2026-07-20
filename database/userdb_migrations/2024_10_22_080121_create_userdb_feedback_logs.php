<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbFeedbackLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('runtime_message_id')->nullable();
            $table->string('message_id')->nullable();
            $table->string('feedback_type');
            $table->string('raw_feedback_content');

            $table->bigInteger('tracking_log_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullalbe();

            $table->foreign('tracking_log_id')->references('id')->on('tracking_logs')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('message_id')->references('message_id')->on('tracking_logs')->onDelete('cascade');

            // Index
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
        Schema::dropIfExists('feedback_logs');
    }
}
