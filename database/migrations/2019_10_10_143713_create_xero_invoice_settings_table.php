<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateXeroInvoiceSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('xero_invoice_settings')) {
            Schema::create('xero_invoice_settings', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('user_id')->nullable();
                $table->integer('account_id')->nullable();
                $table->integer('xero_id')->nullable();
                $table->text('settings')->nullable();
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
        Schema::drop('xero_invoice_settings');
    }
}
