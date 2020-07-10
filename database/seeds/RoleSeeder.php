<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Created by PhpStorm.
 * User: contrive
 * Date: 11/28/17
 * Time: 4:03 PM
 */

class RoleSeeder extends seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('fuse_roles')->where('name', 'Superadmin')->first();

        if (is_null($data)) {
            DB::table('fuse_roles')->insert([
                'name'  => 'Superadmin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('fuse_roles')->insert([
                'name'  => 'User',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
