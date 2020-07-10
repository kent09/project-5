<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFuseInfsProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_infs_products')) {
            Schema::create('fuse_infs_products', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('plan_id');
                $table->boolean('infs_product_id');
                $table->boolean('infs_sub_id');
                $table->integer('charge');
                $table->string('charge_freq', 5);
                $table->softDeletes();
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
        Schema::drop('fuse_infs_products');
    }
}
