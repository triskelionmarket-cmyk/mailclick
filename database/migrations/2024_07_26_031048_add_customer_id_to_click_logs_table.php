<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToClickLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('click_logs', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('tracking_log_id')->unsigned()->nullable();
        });

        // Fill them with data
        $sql = sprintf('UPDATE %s c INNER JOIN %s t ON c.message_id = t.message_id SET c.tracking_log_id = t.id, c.customer_id = t.customer_id WHERE c.tracking_log_id IS NULL', table('click_logs'), table('tracking_logs'));
        DB::statement($sql);

        // Enforce foreign key
        Schema::table('click_logs', function (Blueprint $table) {
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
        Schema::table('click_logs', function (Blueprint $table) {
            //
        });
    }
}
