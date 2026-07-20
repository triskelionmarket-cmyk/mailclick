<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryStatusesToEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->text('delivery_statuses')->nullable();
        });

        // Update all existing campaigns
        foreach(\Acelle\Model\Email::whereNull('delivery_statuses')->get() as $email) {
            $email->setDeliveryStatuses($email->getDefaultDeliveryStatuses());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn('delivery_statuses');
        });
    }
}
