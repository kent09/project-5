<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFuseAdminTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_admin')) {
            Schema::create('fuse_admin', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('setting', 250);
                $table->string('value', 5000);
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
        Schema::drop('fuse_admin');
    }
}
