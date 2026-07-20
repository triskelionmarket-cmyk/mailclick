<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name')->nullable();
        });

        // migrate all user to to old customers
        foreach(\Acelle\Model\Customer::all() as $customer) {
            $user = $customer->users()->first();

            if (!$user) {
                continue;
            }

            $customer->name = substr($user->displayName(get_localization_config('show_last_name_first', $customer->getLanguageCode())), 0, 100);
            $customer->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
}
