<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Plans;
use Illuminate\Support\Facades\Log;

class CreatePlanSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:plan-subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create Subscription plan on stripe';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('start creating plan.');

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $plans = Plans::all();
        
        foreach($plans as $plan) {
            try {
                $stripe_plan = \Stripe\Plan::create([
                    'amount' => $plan->price * 100,
                    'currency' => 'usd',
                    'interval' => substr($plan->billing_period, 0, -2),
                    'product' => ['name' => $plan->label.' '.ucfirst($plan->billing_period)],
                ]);

                $plans = Plans::find($plan->id);
                $plans->stripe_sub_id = $stripe_plan->id;
                $plans->save();
            } catch (\Stripe\Exception\ApiErrorException $e) {

                Log::info($e->getMessage());
    
            }
            

        }

        Log::info('finished creating plan.');
    }
}
