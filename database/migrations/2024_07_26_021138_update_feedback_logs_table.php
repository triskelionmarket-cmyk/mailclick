<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateFeedbackLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bounce_logs', function (Blueprint $table) {
            $sql = sprintf('UPDATE %s f INNER JOIN %s t ON f.message_id = t.message_id SET f.tracking_log_id = t.id, f.customer_id = t.customer_id WHERE f.tracking_log_id IS NULL', table('feedback_logs'), table('tracking_logs'));
            DB::statement($sql);
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
