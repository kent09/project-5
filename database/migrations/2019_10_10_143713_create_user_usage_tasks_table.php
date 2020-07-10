<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserUsageTasksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_usage_tasks')) {
            Schema::create('user_usage_tasks', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('tasks_used')->nullable()->default(0);
                $table->integer('user_id')->unsigned()->index('user_subscriptions_user_id_foreign');
                $table->string('account')->nullable();
                $table->dateTime('reset_date');
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
        Schema::drop('user_usage_tasks');
    }
}
