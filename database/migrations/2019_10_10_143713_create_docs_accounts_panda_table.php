<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsAccountsPandaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_accounts_panda')) {
            Schema::create('docs_accounts_panda', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->text('access_token', 65535);
                $table->string('refresh_token');
                $table->dateTime('expire_date');
                $table->integer('active')->default(1);
                $table->string('api_key', 250)->nullable();
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
        Schema::drop('docs_accounts_panda');
    }
}
