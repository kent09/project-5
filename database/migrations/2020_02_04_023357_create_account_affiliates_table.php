<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountAffiliatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('account_affiliates')) {
            Schema::create('account_affiliates', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('account_id');
                $table->integer('affiliate_user_id');
                $table->date('linked_date');
                $table->timestamps();
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
        Schema::drop('account_affiliates');
    }
}
