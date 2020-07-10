<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcCountriesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_countries')) {
            Schema::create('postc_countries', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('country_name', 100);
                $table->string('country_code', 2);
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
        Schema::drop('postc_countries');
    }
}
