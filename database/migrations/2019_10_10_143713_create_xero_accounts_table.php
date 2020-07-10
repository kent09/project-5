<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXeroAccountsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('xero_accounts')) {
            Schema::create('xero_accounts', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('app_id', 50)->default('');
                $table->integer('user_id');
                $table->string('app_name', 100)->default('');
                $table->string('oauth_token', 200);
                $table->string('oauth_token_secret', 200);
                $table->string('oauth_expires_in')->default('');
                $table->timestamps();
                $table->string('session_handle')->nullable();
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
        Schema::drop('xero_accounts');
    }
}
