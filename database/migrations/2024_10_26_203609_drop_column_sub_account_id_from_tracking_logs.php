<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropColumnSubAccountIdFromTrackingLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('tracking_logs', function (Blueprint $table) {
                $table->dropForeign(['sub_account_id']);
            });
        } catch (\Exception $ex) {
            //
        }

        try {
            Schema::table('tracking_logs', function (Blueprint $table) {
                $table->dropForeign('tracking_logs_sub_account_id_foreign');
            });
        } catch (\Exception $ex) {
            //
        }

        Schema::table('tracking_logs', function (Blueprint $table) {
            $table->dropColumn('sub_account_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tracking_logs', function (Blueprint $table) {
            //
        });
    }
}
