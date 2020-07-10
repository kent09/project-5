<?php

use Illuminate\Database\Seeder;

class AddFreePlansToFusedProductsPlan extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('fuse_plans_products')->insert([
            'plan_id'  => 4,
            'stripe_product_id' => '',
            'stripe_plan_id' => '',
            'charge' => 0,
            'charge_freq' => 'month',
            'priority' => 0
        ]);
    }
}
