<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\UserSubscription;
use App\Plans;

class UpdateMonthlyToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update user subscription token monthly';

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
        $subscription = UserSubscription::where('stripe_status', 'active')->get();
        $plans = Plans::all();

        Log::info('Starting reset the token.');
        foreach($subscription as $subscriber) {
            foreach($plans as $plan) {

                if($subscriber->next_bill_date <= Carbon::now()) {

                    $sub = UserSubscription::find($subscriber->id);

                    if($subscriber->stripe_plan == $plan->stripe_sub_id) { 
        
                        $sub->token_count = $plan->monthly_token_amount;
                        
                    }

                    $date = new Carbon($subscriber->next_bill_date);

                    $sub->next_bill_date = $date->addMonth();
                    $sub->prev_bill_date = $subscriber->next_bill_date;

                    $sub->save();
                }

            }
        }

        Log::info('reset token complete.');
    }
}
