<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubscriptionsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_subscriptions_user_id_foreign');
                $table->boolean('user_plan_id');
                $table->integer('user_product_id')->nullable();
                $table->string('subscription_id', 30);
                $table->integer('status')->comment('0=cancelled,1=active,2=upcoming');
                $table->date('subscription_renew_date');
                $table->softDeletes();
                $table->timestamps();
                $table->string('name');
                $table->string('stripe_id');
                $table->string('stripe_plan');
                $table->integer('quantity');
                $table->dateTime('trial_ends_at')->nullable();
                $table->dateTime('ends_at')->nullable();
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
        Schema::drop('subscriptions');
    }
}
