<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropContactIdFromMailListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('mail_lists', function (Blueprint $table) {
                $table->dropForeign(['contact_id']);
            });
        } catch (\Exception $ex) {
            //
        }

        try {
            Schema::table('mail_lists', function (Blueprint $table) {
                $table->dropForeign('mail_lists_contact_id_foreign');
            });
        } catch (\Exception $ex) {
            //
        }

        Schema::table('mail_lists', function (Blueprint $table) {
            $table->dropColumn('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mail_lists', function (Blueprint $table) {
            //
        });
    }
}
