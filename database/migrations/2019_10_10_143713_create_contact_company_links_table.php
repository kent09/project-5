<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContactCompanyLinksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('contact_company_links')) {
            Schema::create('contact_company_links', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('infs_account_id')->unsigned();
                $table->integer('contact_id')->unsigned()->nullable();
                $table->string('company_id')->nullable();
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
        Schema::drop('contact_company_links');
    }
}
