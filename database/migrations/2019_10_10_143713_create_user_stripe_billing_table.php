<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserStripeBillingTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_stripe_billing')) {
            Schema::create('user_stripe_billing', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('stripe_id', 50)->nullable();
                $table->string('stripe_card_id', 50)->default('');
                $table->string('card_brand', 50)->nullable();
                $table->integer('card_last_four')->nullable();
                $table->timestamps();
                $table->dateTime('deleted_at');
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_stripe_billing');
    }
}
