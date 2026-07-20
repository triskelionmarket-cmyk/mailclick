<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToEmailLinks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('email_links', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        \DB::statement(sprintf("UPDATE %s AS t INNER JOIN %s AS a ON t.email_id = a.id SET t.customer_id = a.customer_id", table('email_links'), table('emails')));
        \DB::statement(sprintf("DELETE FROM %s WHERE customer_id IS NULL", table('email_links')));

        Schema::table('email_links', function (Blueprint $table) {
            $table->integer('customer_id')->unsigned()->nullable(false)->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
