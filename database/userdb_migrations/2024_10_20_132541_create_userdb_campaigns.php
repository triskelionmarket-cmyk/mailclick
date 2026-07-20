<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid')->unique();

            $table->string('name');
            $table->string('type');
            $table->text('subject')->nullable(); // to allow creating empty campaign
            $table->longText('plain')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('status');
            $table->boolean('sign_dkim')->nullable();
            $table->boolean('track_open')->nullable();
            $table->boolean('track_click')->nullable();
            $table->timestamp('run_at')->nullable();
            $table->timestamp('delivery_at')->nullable();
            $table->text('template_source')->nullable();
            $table->longText('last_error')->nullable();
            $table->boolean('use_default_sending_server_from_email')->default(false);
            $table->text('preheader')->nullable();
            $table->integer('running_pid')->default(null)->nullable();
            $table->boolean('skip_failed_message')->default(false);
            $table->text('cache')->nullable();

            $table->bigInteger('default_mail_list_id')->unsigned()->nullable();
            $table->bigInteger('tracking_domain_id')->unsigned()->nullable();
            $table->bigInteger('template_id')->unsigned()->nullable(); // ref master table, no foreign constraints
            $table->bigInteger('customer_id')->unsigned();

            $table->foreign('default_mail_list_id')->nullable()->references('id')->on('mail_lists')->onDelete('cascade');
            $table->foreign('tracking_domain_id')->nullable()->references('id')->on('tracking_domains')->onDelete('set null');
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
        Schema::dropIfExists('campaigns');
    }
}
