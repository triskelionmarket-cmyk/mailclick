<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->string('event');
            $table->string('name');
            $table->string('status');
            $table->integer('setting_retry_times');
            $table->integer('setting_retry_after_seconds');
            $table->string('request_method');
            $table->string('request_url')->nullable();
            $table->string('request_auth_type');
            $table->string('request_auth_bearer_token')->nullable();
            $table->string('request_auth_basic_username')->nullable();
            $table->string('request_auth_basic_password')->nullable();
            $table->string('request_auth_custom_key')->nullable();
            $table->string('request_auth_custom_value')->nullable();
            $table->text('request_headers')->nullable();
            $table->string('request_body_type');
            $table->text('request_body_params')->nullable();
            $table->text('request_body_plain')->nullable();

            $table->timestamps();

            // foreign key
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
