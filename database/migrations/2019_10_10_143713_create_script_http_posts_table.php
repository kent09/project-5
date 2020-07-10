<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScriptHttpPostsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('script_http_posts')) {
            Schema::create('script_http_posts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('infs_account_id');
                $table->integer('contactId')->unsigned()->nullable();
                $table->string('mode', 50);
                $table->string('status', 64)->nullable();
                $table->string('post_data', 16364);
                $table->string('response_data', 5000);
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
        Schema::drop('script_http_posts');
    }
}
