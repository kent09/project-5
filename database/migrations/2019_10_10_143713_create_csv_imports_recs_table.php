<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCsvImportsRecsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        if (!Schema::hasTable('country_owner')) {
//            Schema::create('csv_imports_recs', function (Blueprint $table) {
//                $table->integer('id', true);
//                $table->integer('import_id')->nullable();
//                $table->string('record_type')->nullable();
//                $table->dateTime('raw_datetime')->nullable();
//                $table->text('raw_data')->nullable();
//                $table->dateTime('normalised_datetime')->nullable();
//                $table->text('normalised_data')->nullable();
//                $table->integer('infs_record_id')->nullable();
//                $table->dateTime('infusionsoft_datetime')->nullable();
//                $table->integer('new_existing')->nullable()->comment('new=1,existing=2');
//                $table->integer('status')->nullable()->default(0)->comment('pending=0,inprogress=1,complete=2,error=3');
//                $table->timestamps();
//            });
//        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::drop('csv_imports_recs');
    }
}
