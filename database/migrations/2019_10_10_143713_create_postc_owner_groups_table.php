<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcOwnerGroupsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_owner_groups')) {
            Schema::create('postc_owner_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->nullable();
                $table->integer('infusionsoft_id')->nullable();
                $table->integer('postc_owner_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('status')->comment('1=active,0=delete');
                $table->integer('match_type')->comment('1=radius,2=list');
                $table->string('postc_country_code', 2);
                $table->string('postc_code', 9);
                $table->decimal('postc_radius', 8, 0);
                $table->string('postc_units', 2)->comment('km,mi');
                $table->string('postc_list', 5000);
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
        Schema::drop('postc_owner_groups');
    }
}
