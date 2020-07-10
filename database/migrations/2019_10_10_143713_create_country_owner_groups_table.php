<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCountryOwnerGroupsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('country_owner_groups')) {
            Schema::create('country_owner_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('country_owner_id')->unsigned();
                $table->integer('status')->default(0);
                $table->integer('infs_country_id')->unsigned();
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
        Schema::drop('country_owner_groups');
    }
}
