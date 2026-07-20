<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToBounceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bounce_logs', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('tracking_log_id')->unsigned()->nullable();

            $table->foreign('customer_id')->references('id')->nullable()->on('customers')->onDelete('set null');
            $table->foreign('tracking_log_id')->references('id')->nullable()->on('tracking_logs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bounce_logs', function (Blueprint $table) {
            //
        });
    }
}
