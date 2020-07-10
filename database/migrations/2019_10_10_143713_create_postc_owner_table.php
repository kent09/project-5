<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcOwnerTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_owner')) {
            Schema::create('postc_owner', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('infs_account_id');
                $table->integer('infs_person_id');
                $table->string('owner_name', 50);
                $table->integer('status')->nullable()->comment('1=active,0=delete');
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
        Schema::drop('postc_owner');
    }
}
