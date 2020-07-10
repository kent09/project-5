<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcOwnerSyncTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_owner_sync')) {
            Schema::create('postc_owner_sync', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id');
                $table->integer('infs_account_id');
                $table->integer('status')->comment('0=pending,1=in_progress,2=complete');
                $table->timestamp('start_date_time')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->dateTime('finish_date_time')->default('0000-00-00 00:00:00');
                $table->integer('contacts_updated');
                $table->integer('opp_updated');
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
        Schema::drop('postc_owner_sync');
    }
}
