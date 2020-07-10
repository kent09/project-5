<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountryFallbackOwnersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('country_fallback_owners')) {
            Schema::create('country_fallback_owners', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('fallback_owner_id')->unsigned();
                $table->string('fallback_owner_name');
                $table->integer('user_id')->unsigned();
                $table->integer('infs_account_id')->unsigned();
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
        Schema::drop('country_fallback_owners');
    }
}
