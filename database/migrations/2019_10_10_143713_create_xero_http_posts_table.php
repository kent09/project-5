<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXeroHttpPostsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('xero_http_posts')) {
            Schema::create('xero_http_posts', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->index('user_infusionsoft_trigger_posts_user_id_foreign');
                $table->integer('infs_account_id');
                $table->integer('xero_account_id');
                $table->integer('contactId')->unsigned()->nullable();
                $table->string('xero_contact_id', 150);
                $table->string('post_data', 16364);
                $table->integer('infs_order_id');
                $table->string('infs_order_data', 5000)->comment('products,qtys,prices');
                $table->string('xero_invoice_id', 150);
                $table->string('status', 64)->nullable();
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
        Schema::drop('xero_http_posts');
    }
}
