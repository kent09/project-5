<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserAddressTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_address')) {
            Schema::create('user_address', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->string('first_name', 16)->nullable();
                $table->string('last_name', 16)->nullable();
                $table->string('company_name', 64)->nullable();
                $table->string('phone', 16)->nullable();
                $table->string('address1', 128)->nullable();
                $table->string('address2', 128)->nullable();
                $table->string('city', 32)->nullable();
                $table->string('country', 32)->nullable();
                $table->string('post_code', 32)->nullable();
                $table->text('email_list', 65535)->nullable();
                $table->timestamps();
                $table->string('state');
                $table->string('email')->nullable();
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
        Schema::drop('user_address');
    }
}
