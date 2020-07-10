<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsHttpErrorsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_http_errors')) {
            Schema::create('docs_http_errors', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('infs_account_id');
                $table->text('error_type', 65535)->nullable();
                $table->text('message', 65535);
                $table->text('role_data')->nullable();
                $table->string('document_data', 16364);
                $table->string('template_id')->nullable();
                $table->integer('contactId')->unsigned()->nullable();
                $table->string('status', 64)->nullable();
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
        Schema::drop('docs_http_errors');
    }
}
