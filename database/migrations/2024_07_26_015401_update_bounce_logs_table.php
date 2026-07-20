<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBounceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sql = sprintf('UPDATE %s b INNER JOIN %s t ON b.message_id = t.message_id SET b.tracking_log_id = t.id, b.customer_id = t.customer_id WHERE b.tracking_log_id IS NULL', table('bounce_logs'), table('tracking_logs'));
        DB::statement($sql);
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
