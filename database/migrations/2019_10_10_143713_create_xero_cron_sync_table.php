<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXeroCronSyncTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('xero_cron_sync')) {
            Schema::create('xero_cron_sync', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id');
                $table->integer('xero_id');
                $table->integer('infusionsoft_account_id');
                $table->text('settings', 65535);
                $table->integer('status');
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
        Schema::drop('xero_cron_sync');
    }
}
