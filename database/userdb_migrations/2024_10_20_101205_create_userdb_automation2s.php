<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbAutomation2s extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('automation2s', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->string('name');
            $table->string('time_zone')->nullable();
            $table->string('status');
            $table->longText('data')->nullable();
            $table->text('segment_id')->nullable();
            $table->text('last_error')->nullable();

            $table->bigInteger('mail_list_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('mail_list_id')->references('id')->on('mail_lists')->onDelete('cascade');
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
        Schema::dropIfExists('automation2s');
    }
}
