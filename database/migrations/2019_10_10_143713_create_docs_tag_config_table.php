<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocsTagConfigTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('docs_tag_config')) {
            Schema::create('docs_tag_config', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id');
                $table->integer('infs_account_id');
                $table->string('template_id', 250);
                $table->string('document_status', 100);
                $table->integer('applied_tag_id');
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
        Schema::drop('docs_tag_config');
    }
}
