<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserdbSubscribers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->uuid('uid');

            $table->string('email');
            $table->string('status');
            $table->string('ip')->nullable();
            $table->string('from')->nullable();
            $table->string('subscription_type')->nullable();
            $table->uuid('import_batch_id')->nullable();
            $table->text('tags')->nullable();

            $table->string('verification_status')->nullable();
            $table->string('last_verification_by', 100)->nullable();
            $table->mediumText('last_verification_result')->nullable();
            $table->dateTime('last_verification_at')->nullable();

            // Foreign keys
            $table->bigInteger('mail_list_id')->unsigned();

            // Custom field
            for ($i = 100; $i <= 160; $i += 1) {
                $table->mediumText("custom_{$i}")->nullable();
            }

            // Foreign constraints
            $table->foreign('mail_list_id')->references('id')->on('mail_lists')->onDelete('cascade');


            // Unique indexes
            $table->unique(['mail_list_id', 'email']);

            // Do not make unique index on "uid" or it might slow down INSERT
            $table->index('uid');
            $table->index('email');
            $table->index('status');
            $table->index('verification_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscribers');
    }
}
