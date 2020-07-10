<?php

use Illuminate\Database\Seeder;
use App\Plans;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = $this->plans();

        foreach ($plans as $plan) {
            Plans::updateOrCreate($plan);
        }
    }


    private function plans() {
        return [
            [
                'label' => 'Basic', 
                'monthly_token_amount' => 500,
                'billing_period' => 'monthly',
                'stripe_sub_id' => '',
                'price' => 550,
                'affiliate_commission' => 0.3
            ],
            [
                'label' => 'Basic', 
                'monthly_token_amount' => 500,
                'billing_period' => 'yearly',
                'stripe_sub_id' => '',
                'price' => 6050,
                'affiliate_commission' => 0.3
            ],
            [
                'label' => 'Power User', 
                'monthly_token_amount' => 2000,
                'billing_period' => 'monthly',
                'stripe_sub_id' => '',
                'price' => 990,
                'affiliate_commission' => 0.3
            ],
            [
                'label' => 'Power User', 
                'monthly_token_amount' => 2000,
                'billing_period' => 'yearly',
                'stripe_sub_id' => '',
                'price' => 10890,
                'affiliate_commission' => 0.3
            ],
            [
                'label' => 'Enterprise', 
                'monthly_token_amount' => 5000,
                'billing_period' => 'monthly',
                'stripe_sub_id' => '',
                'price' => 1650,
                'affiliate_commission' => 0.3
            ],
            [
                'label' => 'Enterprise', 
                'monthly_token_amount' => 5000,
                'billing_period' => 'yearly',
                'stripe_sub_id' => '',
                'price' => 18150,
                'affiliate_commission' => 0.3
            ]
        ];
    }
}
