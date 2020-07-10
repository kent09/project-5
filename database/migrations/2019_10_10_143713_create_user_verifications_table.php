<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserVerificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_verifications')) {
            Schema::create('user_verifications', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_verifications_user_id_foreign');
                $table->string('code');
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
        Schema::drop('user_verifications');
    }
}
