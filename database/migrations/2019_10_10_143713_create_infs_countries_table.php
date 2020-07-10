<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInfsCountriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('infs_countries')) {
            Schema::create('infs_countries', function (Blueprint $table) {
                $table->increments('id');
                $table->string('country_name');
                $table->string('country_code')->nullable();
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
        Schema::drop('infs_countries');
    }
}
