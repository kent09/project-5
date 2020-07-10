<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStripeWebhookLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('stripe_webhook_logs')) {
            Schema::create('stripe_webhook_logs', function (Blueprint $table) {
                $table->increments('id');
                $table->text('data', 16777215)->nullable();
                $table->text('response', 16777215)->nullable();
                $table->string('event')->nullable();
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
        Schema::drop('stripe_webhook_logs');
    }
}
