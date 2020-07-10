<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcCodesAuTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_codes_au')) {
            Schema::create('postc_codes_au', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('country_id');
                $table->string('region1', 250);
                $table->string('region2', 250);
                $table->string('region3', 250);
                $table->string('postcode', 10);
                $table->string('suburb', 250);
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
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
        Schema::drop('postc_codes_au');
    }
}
