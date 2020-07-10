<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsAccountsDocsignTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_accounts_docsign')) {
            Schema::create('docs_accounts_docsign', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id')->nullable();
                $table->text('access_token', 65535)->nullable();
                $table->text('refresh_token', 65535)->nullable();
                $table->dateTime('expires_date')->nullable();
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
        Schema::drop('docs_accounts_docsign');
    }
}
