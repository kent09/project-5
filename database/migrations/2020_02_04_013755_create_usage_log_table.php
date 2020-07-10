<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsageLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('usage_log')) {
            Schema::create('usage_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('subscription_id');
                $table->integer('tool_feature_id');
                $table->date('date_time_used');
                $table->integer('usage');
                $table->integer('tokens_used');
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
        Schema::drop('usage_log');
    }
}
