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
        Schema::create('webhook_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->bigInteger('webhook_id')->unsigned()->nullable();
            $table->longText('params');
            $table->string('status')->default(0);
            $table->integer('retries')->default(0);
            
            $table->timestamps();

            // foreign key
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('webhook_id')->references('id')->on('webhooks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_jobs');
    }
};
