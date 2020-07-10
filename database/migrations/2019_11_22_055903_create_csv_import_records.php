<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCsvImportRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('csv_import_records', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('csv_import_id')->nullable();
            $table->integer('infs_contact')->nullable();
            $table->integer('infs_company')->nullable();
            $table->integer('infs_order')->nullable();
            $table->string('con_created_matched')->nullable();
            $table->string('comp_created_matched')->nullable();
            $table->string('order_created_matched')->nullable();
            $table->longText('data')->nullable();
            $table->integer('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('csv_import_records');
    }
}
