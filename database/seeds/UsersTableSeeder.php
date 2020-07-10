<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Updated by Cres Mstr
 * Created by PhpStorm.
 * User: contrive
 * Date: 11/28/17
 * Time: 12:46 PM
 */

class UsersTableSeeder extends seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('users')->where('email', 'admin@fusedsoftware.com')->first();

        if (is_null($data)) {
            $data = DB::table('fuse_roles')->where('name', 'Superadmin')->first();

            DB::table('users')->insert([
                'email'         => 'admin@fusedsoftware.com',
                'password'      => bcrypt(12345678),
                'first_name'    => 'Fused',
                'last_name'     => 'Admin',
                'role_id'          => $data->id,
                'active'        => 1,
                'company_name'  => 'Fusedtools',
                 'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
