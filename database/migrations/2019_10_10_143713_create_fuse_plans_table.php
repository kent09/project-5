<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFusePlansTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_plans')) {
            Schema::create('fuse_plans', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 32);
                $table->integer('monthly_task_limit')->unsigned();
                $table->integer('daily_record_limit')->comment('maximum number of csv records processed per day');
                $table->integer('monthly_doc_limit');
                $table->integer('infs_account_limit');
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
        Schema::drop('fuse_plans');
    }
}
