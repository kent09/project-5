<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsCompletedTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_completed')) {
            Schema::create('docs_completed', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('infs_account_id');
                $table->string('document_id')->nullable();
                $table->string('template_id')->nullable();
                $table->string('http_post_log', 16364);
                $table->string('contactId')->nullable();
                $table->timestamps();
                $table->string('type')->nullable();
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
        Schema::drop('docs_completed');
    }
}
