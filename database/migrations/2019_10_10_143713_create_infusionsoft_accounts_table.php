<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInfusionsoftAccountsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('infusionsoft_accounts')) {
            Schema::create('infusionsoft_accounts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->string('name')->nullable();
                $table->text('access_token', 65535)->nullable();
                $table->text('referesh_token', 65535)->nullable();
                $table->dateTime('expire_date')->nullable();
                $table->string('account')->nullable();
                $table->integer('active')->default(1);
                $table->timestamps();
                $table->string('client_id')->nullable();
                $table->string('client_secret')->nullable();
                $table->boolean('error_reported')->default(0);
                $table->integer('is_default')->default(0);
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
        Schema::drop('infusionsoft_accounts');
    }
}
