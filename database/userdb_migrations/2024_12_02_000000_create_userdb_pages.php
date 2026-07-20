<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->longText('content');
            $table->text('subject');
            $table->boolean('use_outside_url')->default(false);
            $table->text('outside_url')->nullable();

            $table->bigInteger('customer_id')->unsigned();
            $table->bigInteger('mail_list_id')->unsigned();
            $table->bigInteger('layout_id')->unsigned(); // reference master db

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
        Schema::dropIfExists('emails');
    }
}
