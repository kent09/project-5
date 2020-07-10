<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDocumentStageSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('document_stage_settings')) {
            Schema::create('document_stage_settings', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned()->nullable()->index('document_stage_settings_user_id_foreign');
                $table->string('temp_id')->nullable();
                $table->string('document_status')->nullable();
                $table->string('infs_opportunity_stage')->nullable();
                $table->boolean('create_opp_if_not_exists')->default(0);
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
        Schema::drop('document_stage_settings');
    }
}
