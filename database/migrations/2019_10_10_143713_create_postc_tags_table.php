<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePostcTagsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('postc_tags')) {
            Schema::create('postc_tags', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('infs_account_id');
                $table->integer('status')->nullable()->comment('-1=error 1=active,0=deleted, 2=Pending, 3=In Progress');
                $table->integer('postc_type')->comment('1=radius,2=list');
                $table->string('postc_country_code', 2);
                $table->string('postc_code', 9);
                $table->decimal('postc_radius', 8, 0);
                $table->string('postc_units', 2)->comment('km,mi');
                $table->string('postc_list', 5000);
                $table->integer('tag_id');
                $table->integer('tag_count')->nullable()->default(0);
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
        Schema::drop('postc_tags');
    }
}
