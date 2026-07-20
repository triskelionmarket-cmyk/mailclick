<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailVerificationCreditsToPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('email_verification_credits')->nullable();
        });

        // Migration
        foreach(\Acelle\Model\PlanGeneral::all() as $plan) {
            $plan->email_verification_credits = $plan->getOption('verification_credits_limit') ?? 0;
            $plan->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('email_verification_credits');
        });
    }
}
