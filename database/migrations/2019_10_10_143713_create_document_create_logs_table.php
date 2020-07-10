<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocumentCreateLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('document_create_logs')) {
            Schema::create('document_create_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('status')->default(0);
                $table->string('app')->nullable();
                $table->string('error_message')->nullable();
                $table->text('data', 16777215)->nullable();
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
        Schema::drop('document_create_logs');
    }
}
