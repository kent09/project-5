<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserPasswordResetsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_password_resets')) {
            Schema::create('user_password_resets', function (Blueprint $table) {
                $table->string('email')->index('password_resets_email_index');
                $table->string('token')->index('password_resets_token_index');
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::drop('user_password_resets');
    }
}
