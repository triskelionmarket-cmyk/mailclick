<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTriggerSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trigger_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->integer('automation2_id')->unsigned();
            $table->timestamps();

            $table->foreign('automation2_id')->references('id')->on('automation2s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trigger_sessions');
    }
}
