<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXeroCronSyncResultTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('xero_cron_sync_result')) {
            Schema::create('xero_cron_sync_result', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('xero_cron_sync_id');
                $table->integer('status')->comment('1=pending, 2=in progress, 3=complete');
                $table->timestamp('start_time')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('end_time')->nullable();
                $table->integer('orders_synced');
                $table->dateTime('next_scheduled')->nullable();
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
        Schema::drop('xero_cron_sync_result');
    }
}
