<?php
namespace App\Http\Controllers;

use App\User;
use App\UserAddress;
use App\Http\Requests;
use App\FusePlans;
use App\Plans;
use App\FuseInfsProduct;
use App\FusePlanProduct;
use App\UserSubscription;
use App\StripeWebhookLog;
use App\Services\InfusionSoftService;
use App\Services\UserSubscriptionService;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Infusionsoft\Infusionsoft;
use Laravel\Cashier\Subscription;
use Carbon\Carbon;
use Stripe\Stripe;
use Session;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;

class StripeWebhookController extends CashierController
{
    protected $userSubscriptionService;
    protected $infusionSoftService;

    /** Constructor */
    public function __construct(UserSubscriptionService $userSubscriptionService)
    {
        if (config('cashier.webhook.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
        $this->userSubscriptionService = $userSubscriptionService;
    }
   

    public function webhook(Request $request)
    {
        $data = $request->input();
        $responseCode = "";
        $responseMessage = "";
    
        //handle grace period expiration
        if (isset($data['type']) && $data['type'] == 'customer.subscription.deleted') { // a grace period expires!
            $log = StripeWebhookLog::create(['data' => json_encode($data), 'event' => $data['type']]);
            
            $subscriptionId = ($data['data']['object']['id']);

            //get subscription record and user based on subscription record
            $subscriptionId = 'sub_DZoEgLTHTzwanU';
            $subscription = UserSubscription::where('stripe_id', $subscriptionId)->first();
            
            if (!$subscription) {
                $responseCode = 404;
                $responseMessage = $responseCode . " - " . 'NO RESULTS FOUND FOR SUBSCRIPTION ID: ' . $subscriptionId;
                $log->response = $responseMessage;
                $log->save();
                return response($responseMessage, $responseCode);
            }
            $user = User::find($subscription->user_id);
            $subscription->status = 0;
            $subscription->save();
            UserSubscription::where('user_id', $user->id)->where('status', 2)->update(['status' => 1]);

            //look for active subscription, if no result, reduce to free (user_plan_id 4)
            $activeSubscription = UserSubscription::where('user_id', $user->id)->where('status', 1)->first();
            if (!$activeSubscription) {
                UserSubscription::where('user_id', $user->id)->where('user_plan_id', 4)->update(['status' => 1]);
            }

            $log->response = "OK";
            $log->save();
        }

        //handle successful recurring charge. We just need to ping them 200 or else they will not charge
        elseif (isset($data['type']) && $data['type'] == 'invoice.created') {
            $log = StripeWebhookLog::create(['data' => json_encode($data), 'event' => $data['type']]);
            $log->response = "OK";
            $log->save();
        }

        //handle failed payment, notify customer
        elseif (isset($data['type']) && $data['type'] == 'invoice.payment_failed') {
            $log = StripeWebhookLog::create(['data' => json_encode($data), 'event' => $data['type']]);
            $log->response = "OK";
            $log->save();
        }
        return response('OK', 200);

        //var_dump();
    }

    /**
     * Handle customer subscription updated.
     *
     * @param  array  $payload
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        if ($user = $this->getUserByStripeId($payload['data']['object']['customer'])) {
            $data = $payload['data']['object'];

            $user->subscriptions->filter(function (Subscription $subscription) use ($data) {
                return $subscription->stripe_id === $data['id'];
            })->each(function (Subscription $subscription) use ($data) {
                if (isset($data['status']) && $data['status'] === 'incomplete_expired') {
                    $subscription->delete();

                    return;
                }

                // Quantity...
                if (isset($data['quantity'])) {
                    $subscription->quantity = $data['quantity'];
                }

                // Plan...
                if (isset($data['plan']['id'])) {
                    
                    $account = Accounts::firstOrCreate(['owner_id' => $subscription->user_id]);
                    $plan = Plans::where('stripe_sub_id', $data['plan']['id'])->first();
                    $prev_plan = Plans::where('stripe_sub_id', $subscription->stripe_plan)->first();

                    $diff_token = $plan->monthly_token_amount - $prev_plan->monthly_token_amount;

                    $subscription->stripe_plan = $data['plan']['id'];
                    $subscription->user_plan_id = $plan->id;
                    $subscription->account_id = $account->id;

                    if($prev_plan->id < $plan->id || $prev_plan->id > $plan->id) {
                        $subscription->token_count = $subscription->token_count + $diff_token;
                    }

                }

                // Trial ending date...
                if (isset($data['trial_end'])) {
                    $trial_ends = Carbon::createFromTimestamp($data['trial_end']);

                    if (! $subscription->trial_ends_at || $subscription->trial_ends_at->ne($trial_ends)) {
                        $subscription->trial_ends_at = $trial_ends;
                    }
                }

                // Cancellation date...
                if (isset($data['cancel_at_period_end'])) {
                    if ($data['cancel_at_period_end']) {
                        $subscription->ends_at = $subscription->onTrial()
                            ? $subscription->trial_ends_at
                            : Carbon::createFromTimestamp($data['current_period_end']);
                    } else {
                        $subscription->ends_at = null;
                    }
                }

                // Status...
                if (isset($data['status'])) {
                    $subscription->stripe_status = $data['status']; 
                }

                $subscription->save();
            });
        }

        return $this->successMethod();
    }

    /* END)
    }
    /* END OF REFACTORE CODE */
}
