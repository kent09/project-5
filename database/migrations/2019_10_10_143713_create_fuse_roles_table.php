<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFuseRolesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_roles')) {
            Schema::create('fuse_roles', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 16);
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
        Schema::drop('fuse_roles');
    }
}
