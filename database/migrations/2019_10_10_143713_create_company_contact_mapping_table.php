<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCompanyContactMappingTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('company_contact_mapping')) {
            Schema::create('company_contact_mapping', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('infs_account_id')->unsigned();
                $table->string('company_field_map');
                $table->string('contact_field_map')->nullable();
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
        Schema::drop('company_contact_mapping');
    }
}
