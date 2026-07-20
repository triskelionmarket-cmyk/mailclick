<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbMailLists extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_lists', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->string('name');
            $table->string('from_email');
            $table->string('from_name');
            $table->text('remind_message')->nullable();
            $table->text('email_subscribe')->nullable();
            $table->text('email_unsubscribe')->nullable();
            $table->text('email_daily')->nullable();
            $table->boolean('send_welcome_email')->default(false);
            $table->boolean('unsubscribe_notification')->default(false);
            $table->boolean('subscribe_confirmation')->default(true);
            $table->text('cache')->nullable();
            $table->string('status')->nullable();
            $table->boolean('all_sending_servers')->nullable()->default(false);
            $table->text('embedded_form_options')->nullable();

            $table->bigInteger('customer_id')->unsigned();
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
        Schema::dropIfExists('mail_lists');
    }
}
