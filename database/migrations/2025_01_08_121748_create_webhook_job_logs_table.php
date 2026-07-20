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
        Schema::create('webhook_job_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->bigInteger('webhook_job_id')->unsigned()->nullable();
            $table->longText('request_details');
            $table->string('response_http_code');
            $table->longText('response_content');
            $table->longText('response_error')->nullable();
            
            $table->timestamps();

            // foreign key
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('webhook_job_id')->references('id')->on('webhook_jobs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_job_logs');
    }
};
