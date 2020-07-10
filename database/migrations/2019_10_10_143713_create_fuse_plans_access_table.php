<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFusePlansAccessTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_plans_access')) {
            Schema::create('fuse_plans_access', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('plan_id');
                $table->string('function_name', 32);
                $table->integer('restrict_access')->comment('1=yes,0=no');
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
        Schema::drop('fuse_plans_access');
    }
}
