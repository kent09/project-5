<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocumentAdditionalInfsFieldsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('document_additional_infs_fields')) {
            Schema::create('document_additional_infs_fields', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->nullable()->index('document_additional_infs_fields_user_id_foreign');
                $table->string('temp_id')->nullable();
                $table->string('type')->nullable();
                $table->string('contact_field')->nullable();
                $table->string('opportunity_field')->nullable();
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
        Schema::drop('document_additional_infs_fields');
    }
}
