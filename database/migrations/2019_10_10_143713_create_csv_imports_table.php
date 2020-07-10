<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCsvImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('csv_imports')) {
            Schema::create('csv_imports', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id')->nullable();
                $table->integer('account_id')->nullable();
                $table->string('import_title', 100)->nullable();
                $table->integer('status')->nullable()->default(0)->comment('uploading=0,pending=1,inprogress=2,complete=3');
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->string('csv_file')->nullable();
                $table->longText('csv_settings')->nullable();
                $table->longText('import_results')->nullable();
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
        Schema::drop('csv_imports');
    }
}
