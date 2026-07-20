<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixSiteOnlineSettingIssue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')
            ->where('name', 'site_online')
            ->where('value', 'true')
            ->update(['value' => 'yes']);

        DB::table('settings')
            ->where('name', 'site_online')
            ->where('value', 'false')
            ->update(['value' => 'no']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
