<?php

use Illuminate\Database\Seeder;

class UpdateFusedPlansProductsWithPriorities extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('fuse_plans_products')
        ->where('id', 1)
        ->update(['priority' => 1, 'stripe_plan_id' => 'ft-basic-monthly']);

        DB::table('fuse_plans_products')
        ->where('id', 2)
        ->update(['priority' => 2, 'stripe_plan_id' => 'ft-basic-annual']);

        DB::table('fuse_plans_products')
        ->where('id', 3)
        ->update(['priority' => 3, 'stripe_plan_id' => 'ft-power-monthly']);

        DB::table('fuse_plans_products')
        ->where('id', 4)
        ->update(['priority' => 4, 'stripe_plan_id' => 'ft-power-annual']);

        DB::table('fuse_plans_products')
        ->where('id', 5)
        ->update(['priority' => 5, 'stripe_plan_id' => 'ft-agency-monthly']);

        DB::table('fuse_plans_products')
        ->where('id', 6)
        ->update(['priority' => 6, 'stripe_plan_id' => 'ft-agency-annual']);
    }
}
