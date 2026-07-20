<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToOpenLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add columns
        Schema::table('open_logs', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('tracking_log_id')->unsigned()->nullable();
        });

        // Fill them with data
        $sql = sprintf('UPDATE %s o INNER JOIN %s t ON o.message_id = t.message_id SET o.tracking_log_id = t.id, o.customer_id = t.customer_id WHERE o.tracking_log_id IS NULL', table('open_logs'), table('tracking_logs'));
        DB::statement($sql);

        // Enforce foreign key
        Schema::table('open_logs', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('tracking_log_id')->references('id')->on('tracking_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_logs', function (Blueprint $table) {
            //
        });
    }
}
