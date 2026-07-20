<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->string('subject')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->boolean('sign_dkim')->nullable();
            $table->boolean('track_open')->nullable();
            $table->boolean('track_click')->nullable();
            $table->string('action_id')->nullable();
            $table->longtext('plain')->nullable();
            $table->boolean('skip_failed_message')->default(false);
            $table->text('preheader')->nullable();
            $table->boolean('use_default_sending_server_from_email')->default(false);

            $table->bigInteger('automation2_id')->unsigned();
            $table->bigInteger('tracking_domain_id')->unsigned()->nullable();
            $table->bigInteger('template_id')->unsigned()->nullable(); // master table
            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
            $table->foreign('tracking_domain_id')->references('id')->on('tracking_domains')->onDelete('set null');
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
        Schema::dropIfExists('emails');
    }
}
