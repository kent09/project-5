<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('account_users')) {
            Schema::create('account_users', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('account_id');
                $table->integer('user_id');
                $table->integer('access_level');
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
        Schema::drop('account_users');
    }
}
