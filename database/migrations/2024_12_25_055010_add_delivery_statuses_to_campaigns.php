<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryStatusesToCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->text('delivery_statuses')->nullable();
        });

        // Update all existing campaigns
        foreach(\Acelle\Model\Campaign::whereNull('delivery_statuses')->get() as $campaign) {
            $campaign->setDeliveryStatuses($campaign->getDefaultDeliveryStatuses());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('delivery_statuses');
        });
    }
}
