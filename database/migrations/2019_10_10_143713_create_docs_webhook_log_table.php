<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsWebhookLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_webhook_log')) {
            Schema::create('docs_webhook_log', function (Blueprint $table) {
                $table->integer('id', true);
                $table->timestamps();
                $table->text('data', 16777215)->nullable();
                $table->integer('status')->nullable();
                $table->text('error_message', 65535)->nullable();
                $table->integer('user_id')->unsigned()->nullable();
                $table->integer('infs_account_id')->nullable();
                $table->string('completed_id')->nullable();
                $table->string('document_status')->nullable();
                $table->integer('contactId')->unsigned()->nullable();
                $table->integer('tag_applied')->nullable();
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
        Schema::drop('docs_webhook_log');
    }
}
