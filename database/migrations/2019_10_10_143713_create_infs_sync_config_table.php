<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInfsSyncConfigTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('infs_sync_config')) {
            Schema::create('infs_sync_config', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('infs_account_id')->unsigned();
                $table->boolean('company_add')->nullable()->default(0);
                $table->boolean('company_edit')->nullable()->default(0);
                $table->boolean('contact_add')->nullable()->default(0);
                $table->boolean('contact_edit')->nullable()->default(0);
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
        Schema::drop('infs_sync_config');
    }
}
