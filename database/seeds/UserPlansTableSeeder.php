<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UserPlansTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = DB::table('fuse_plans')->where('name', 'Professional')->first();
        if (is_null($data)) {
            DB::table('fuse_plans')->insert([
                'name' => 'Free',
                'monthly_task_limit' => 50,
                'daily_record_limit' => 100,
                'monthly_doc_limit' => 10,
                'infs_account_limit' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('fuse_plans')->insert([
                'name' => 'Power User',
                'monthly_task_limit' => 500,
                'daily_record_limit' => 1000,
                'monthly_doc_limit' => 500,
                'infs_account_limit' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('fuse_plans')->insert([
                'name' => 'Agency',
                'monthly_task_limit' => 1500,
                'daily_record_limit' => 2500,
                'monthly_doc_limit' => 1500,
                'infs_account_limit' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            DB::table('fuse_plans')->insert([
                'name' => 'Basic',
                'monthly_task_limit' => 150,
                'daily_record_limit' => 500,
                'monthly_doc_limit' => 50,
                'infs_account_limit' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
