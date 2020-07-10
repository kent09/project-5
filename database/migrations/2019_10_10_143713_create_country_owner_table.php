<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountryOwnerTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('country_owner')) {
            Schema::create('country_owner', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('infs_account_id')->unsigned();
                $table->integer('status')->default(0);
                $table->integer('infs_person_id')->unsigned();
                $table->timestamps();
                $table->string('owner_name');
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
        Schema::drop('country_owner');
    }
}
