<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountIdToSubscriptionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('account_id');
            $table->timestamp('next_bill_date');
            $table->timestamp('prev_bill_date');
            $table->string('stripe_status')->nullable();
            $table->float('token_count',8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->dropColumn('token_count');
            $table->dropColumn('stripe_status');
            $table->timestamp('next_bill_date');
            $table->timestamp('prev_bill_date');
        });
    }
}
