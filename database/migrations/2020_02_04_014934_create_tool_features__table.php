<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateToolFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tool_features')) {
            Schema::create('tool_features', function (Blueprint $table) {
                $table->increments('id');
                $table->string('tool');
                $table->boolean('enabled');
                $table->string('description');
                $table->integer('token_cost');
                $table->integer('amount_per');
                $table->string('amount_unit');
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
        Schema::drop('tool_features');
    }
}
