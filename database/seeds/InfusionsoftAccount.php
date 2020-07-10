<?php

use Illuminate\Database\Seeder;
use App\InfsAccount;

class InfusionsoftAccount extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('users')->where('email', 'admin@fusedsoftware.com')->first();

        $infsAccount = new InfsAccount;
        $infsAccount->user_id = $data->id;
        $infsAccount->name = 'wb323';
        $infsAccount->access_token = 'xvrczmpff9kt44bjysnmtu6z';
        $infsAccount->account = 'wb323.infusionsoft.com';
        $infsAccount->active = 1;
        $infsAccount->save();
    }
}
