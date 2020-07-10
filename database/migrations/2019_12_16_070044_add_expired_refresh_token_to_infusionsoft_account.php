<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiredRefreshTokenToInfusionsoftAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('infusionsoft_accounts', function($table) {
            $table->dateTime('refresh_token_expired_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('infusionsoft_accounts', function($table) {
            $table->dropColumn('refresh_token_expired_at');
        });
    }
}
