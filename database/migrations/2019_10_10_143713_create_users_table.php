<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('remember_token', 100)->nullable();
                $table->string('invitation_token', 250);
                $table->boolean('active')->default(0);
                $table->string('FuseKey')->nullable();
                $table->smallInteger('infusionsoft_contact_id')->unsigned();
                $table->integer('role_id')->unsigned()->nullable();
                $table->string('timezone')->nullable();
                $table->timestamps();
                $table->string('first_name', 16)->nullable();
                $table->string('last_name', 16)->nullable();
                $table->string('company_name', 64)->nullable();
                $table->string('phone', 16)->nullable();
                $table->string('address1', 128)->nullable();
                $table->string('address2', 128)->nullable();
                $table->string('city', 32)->nullable();
                $table->string('country', 32)->nullable();
                $table->string('post_code', 32)->nullable();
                $table->smallInteger('infusion_soft_contact_id')->unsigned();
                $table->boolean('free_docs')->default(10);
                $table->string('state')->nullable();
                $table->string('stripe_id')->nullable();
                $table->string('card_brand')->nullable();
                $table->string('card_last_four')->nullable();
                $table->dateTime('trial_ends_at')->nullable();
                $table->string('domain_activated')->nullable();
                $table->dateTime('activated_date')->nullable();
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
        Schema::drop('users');
    }
}
