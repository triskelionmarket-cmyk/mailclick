<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbTimelines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->text('activity')->nullable();
            $table->text('activity_type')->nullable();
            $table->text('url')->nullable();
            $table->string('type')->nullable();

            $table->bigInteger('automation2_id')->unsigned()->nullable();
            $table->bigInteger('subscriber_id')->unsigned();
            $table->bigInteger('auto_trigger_id')->unsigned()->nullable();
            $table->bigInteger('mail_list_id')->unsigned()->nullable();
            $table->bigInteger('form_id')->unsigned()->nullable();
            $table->bigInteger('campaign_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
            $table->foreign('subscriber_id')->references('id')->on('subscribers')->onDelete('cascade');
            $table->foreign('auto_trigger_id')->references('id')->on('auto_triggers')->onDelete('cascade');
            $table->foreign('mail_list_id')->references('id')->on('mail_lists')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');

            // INDEX
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timelines');
    }
}
