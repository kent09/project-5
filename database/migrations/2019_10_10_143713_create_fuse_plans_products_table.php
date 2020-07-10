<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFusePlansProductsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('fuse_plans_products')) {
            Schema::create('fuse_plans_products', function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('plan_id');
                $table->string('stripe_product_id', 50);
                $table->string('stripe_plan_id', 50);
                $table->integer('charge');
                $table->string('charge_freq', 5);
                $table->softDeletes();
                $table->timestamps();
                $table->integer('priority');
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
        Schema::drop('fuse_plans_products');
    }
}
