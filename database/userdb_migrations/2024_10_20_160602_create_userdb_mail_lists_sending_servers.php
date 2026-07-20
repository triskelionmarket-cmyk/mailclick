<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbMailListsSendingServers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_lists_sending_servers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->integer('fitness');
            $table->bigInteger('sending_server_id')->unsigned();
            $table->bigInteger('mail_list_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('mail_list_id')->references('id')->on('mail_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_lists_sending_servers');
    }
}
