<?php
namespace App\Services;

use App\PaymentHistory;
use App\User;
use App\UserAddress;
use App\FusePlans;
use App\UserSubscription;
use App\FusePlanProduct;
use App\UserUsageCsv;
use App\UserUsageDocs;
use App\UserUsageTasks;
use App\Accounts;
use App\Plans;
use App\AccountBilling;
use App\Http\Controllers\InfusionSoftAuthController;
use App\Services\InfusionSoftService;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Infusionsoft\Infusionsoft;
use Carbon\Carbon;
use Stripe;

class UserSubscriptionService
{
    protected $infusionSoftService;
    protected $userService;
    protected $planService;
    protected $helper;
    protected $mailService;

    public function __construct(
        InfusionSoftService $infusionSoftService,
        UserService $userService,
        PlanService $planService,
        Helpers $helper,
        MailService $mailService
    ) {
        $this->infusionSoftService = $infusionSoftService;
        $this->userService = $userService;
        
        $this->planService = $planService;
        $this->helper = $helper;
        $this->mailService = $mailService;

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }

    public function update_payment($request, $user) {
        try {

            \Stripe\PaymentMethod::update(
                $request->paymentid,
                ['billing_details' => 
                    [
                        'name' => $request->first_name.' '.$request->last_name,
                        'email' => $user->email
                    ]
                ]
            );
            return ["status" => "success"];
        
        } catch (\Stripe\Exception\ApiErrorException $e) {

            return ["status" => "error", "msg" => $e->getMessage() ]; 
            
        }
        
    }

    public function add_payment_method($paymentid) {
        
        $user = Auth::user();
        try {

            $paymentMethod = $user->addPaymentMethod($paymentid);

            return ['status' => 'success'];

        } catch (\Stripe\Exception\CardException $e) {

            return ['status' => 'error', 'msg' => $e->getMessage() ];

        }

    }

    public function send_email($user, $plan) {

        $subject = "FusedTools Notifications";
        $email = $user->email;

        \Mail::send('emails.userSubscribe', [ 'name' => $user->useraddress->first_name, 'product' => $plan], function ($message) use ($email, $subject) {
            $message->to($email)->bcc('help@fusedtools.com')->subject($subject);
        });
        
    }

    public function retrieveSubscription($subscription) {

        try {
            $data = \Stripe\Subscription::retrieve(
                $subscription->stripe_id
            );

            return ['status' => 'success', 'msg' => $data];

        } catch (\Stripe\Exception\ApiErrorException $e) {
            return ['status' => 'error', 'msg' => $e->getMessage() ];
        }
    }


    public function newSubscription($user, $plan, $account, $request) {
        
        try {

            $data = $user->newSubscription('main', $plan->stripe_sub_id)->create($request->paymentid, [
                'metadata' => $plan->label.' '.$plan->billing
            ]);

        } catch (IncompletePayment $exception) {

            return ["status" => "error", "msg" => $exception->payment->id];

        }

        $retriev_sub = $this->retrieveSubscription($data);

        if($retriev_sub['status'] == "error") {
            return ['status' => 'error', 'msg' => $retriev_sub['msg'] ];
        }
        
        // add new subscription
        $subscription = UserSubscription::find($data->id);
        $subscription->prev_bill_date = Carbon::createFromTimestamp($retriev_sub['msg']->current_period_start);
        $subscription->next_bill_date = Carbon::createFromTimestamp($retriev_sub['msg']->current_period_end);
        $subscription->user_plan_id = $plan->id;
        $subscription->account_id = $account->id;
        $subscription->token_count = $plan->monthly_token_amount;
        $subscription->save();
   
        return ["status" => "success"];
    }
    
    public function subscribe($request)
    {
        
        $user = Auth::user();
        $account = Accounts::firstOrCreate(['owner_id' => $user->id]);
        $product = Plans::findOrFail($request->planid);
        $subscription = UserSubscription::where('account_id', $account->id)->first();
     
        if($subscription && $request->planid == $subscription->user_plan_id) {
            return ['status' => 'error', 'msg' => 'You are currently subscribed to this plan.'];
        }

        if(!$subscription) {

            try {

                $result = \Stripe\Subscription::create(
                    [
                        'customer' => $user->stripe_customer_id,
                        'items' => [['plan' => $product->stripe_sub_id]]
                    ]
                );


                $subscription = new UserSubscription;
                $subscription->user_plan_id = $product->id;
                $subscription->account_id = $account->id;
                $subscription->subscription_id = $result->id;
                $subscription->stripe_plan = $product->stripe_sub_id;
                $subscription->trial_ends_at = $result->trial_end;
                $subscription->token_count = $product->monthly_token_amount;
                $subscription->ends_at = $result->ended_at;
                $subscription->status = 1;
                $subscription->save();

                return ['status' => 'success', 'msg' => 'You have successfully subscribed to '.$product->label];
        
            } catch (\Stripe\Error\Card $e) {
    
                return ['status' => 'error', 'msg' => $e->getMessage()];
    
            }

        }
        
        return $this->changePlan($request, $product, $user);
        
    }

    /**
    * A function that stores the record to  address and perform entry point to stripe buy subscription
    * @return boolean
    *   true = successful payment
    *   false = error in stripe, flash error code is stored.
    *
    * Error code:
    *   ERR-001 - Card error
    */
    
    public function changePlan($request, $product, $user)
    {

        // $response = ['result' => false];

        // $stripeTokenId = $stripeToken ?? false;

        // $result = $this->buySubscription($user->id, $product, $user->stripe_id);

        $result = $this->updateSubscription($product);

        // if (isset($result['stripeResult']['stripe_id']) && !empty($result['stripeResult']['stripe_id'])) {
        if($result['status'] == 'success') {
            \Mail::send('emails.userSubscribe', [ 'name' => $user->userAddress->first_name, 'product' => $product], function ($message) {
                $message->from('help@fusedtools.com', 'FusedTools');
                $message->to($user->email)->bcc("help@fusedtools.com")->subject('FusedTools Notifications');
            });
            $response['message'] = 'New Plan Request saved Successfully.';
            $response['result'] = true;
            $response['code'] = '0000';
            return $response;
        } else {
            $response['message'] = isset($result['message']) ? $result['message'] : 'There was an error during the transaction';
            $response['result'] = false;
            $response['code'] = 'ERR-0001';
            return $response;
        }

    }

    protected function updateSubscription($product) {

        $account = Accounts::firstOrCreate(['owner_id' => Auth::id()]);
        $subscription = UserSubscription::where('account_id', $account->id)->first();

        try {

            \Stripe\Subscription::update(
                $subscription->subscription_id,
                ['items' => ['plan' => $product->stripe_sub_id ]]
            );

        } catch (\Stripe\Error\Card $e) {

            return ['status' => 'error', 'msg' => $e->getMessage()];

        }

        // $plan = Plans::find($subscription->user_plan_id);
        // if($plan->monthly_token_amount > $product->monthly_token_amount ) {
        //     $subscription->token_count = $subscription->token_count + $product->monthly_token_amount;
        // } else {
        //     $subscription->token_count = $subscription->token_count - $product->monthly_token_amount;
        // }

        // $subscription->save();

        return ['status' => 'success', 'msg' => 'You have successfully updated your subscription to '.$product->label];
          
    }



    /**
    * Perform the API calling of subscription to stripe
    *
    * @return boolean if API succeed or not
    */
    protected function buySubscription($userID, $plan, $stripeTokenId = false)
    {

        $user = User::where('id', $userID)->first();
        $exitsingSubscription = UserSubscription::where("user_id", $userID)->whereIn("status", [1, 2])->orderBy('status', 'DESC')->first();
        $subscriptionCount = UserSubscription::where("user_id", $userID)->count();

        $stripeResult = null;

        //it means this is the first time the user will subscribe so we can assume that existing subscription plan is FREE

        if ($subscriptionCount == 1) {
            if (isset($exitsingSubscription->user_product_id) && $plan->id == $exitsingSubscription->user_product_id) {
                return ['result' => false, 'message' => 'You are currently subscribed to this plan.'];
            }
            
            try {
                $stripeResult = $user->newSubscription('main', $plan['stripe_plan_id'])->create($stripeTokenId);
            } catch (\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                return ['result' => false, 'message' => $body['error']['message']];
            }
            
            if (isset($stripeResult['stripe_id']) && !empty($stripeResult['stripe_id'])) {
                $renewDate = date("Y-m-d 00:00:00", strtotime("-1 day", strtotime("+1 ".$plan->charge_freq)));
                UserSubscription::where('user_id', $userID)->update(['status' => 0]);
                UserSubscription::updateOrCreate(["user_id"=>$userID, 'stripe_id' => $stripeResult['stripe_id']], ["user_plan_id"=>$plan->plan_id, "subscription_id" => $stripeResult['stripe_id'], 'user_product_id' => $plan->id , "status"=>1, "subscription_renew_date"=>$renewDate]);
                
                $resetDate = Carbon::now()->addmonth()->hour(0)->minute(0)->second(0);
                //add or update user usage since the plan is getting changed
                UserUsageCsv::updateOrCreate(["user_id"=> $userID], ["records_imported" => 0, "reset_date"=>$resetDate]);
                UserUsageDocs::updateOrCreate(["user_id"=> $userID], ["docs_sent" => 0, "reset_date"=>$resetDate]);
                UserUsageTasks::updateOrCreate(["user_id"=> $userID], ["tasks_used" => 0, "reset_date"=>$resetDate]);
            } else {
                return ['result' => false, 'message' => 'Stripe API failed'];
            }
        } else { //we decide whether it is upgrade or downgrade according to priority field,
            $targetPriority = $plan->priority;
            $currentPriority = $exitsingSubscription->product->priority;

            if ($targetPriority == $currentPriority) { // equal priority means product is same so we will throw error
                return ['result' => false, 'message' => 'You are currently subscribed to this plan.'];
            }
             
            if ($targetPriority > $currentPriority) { //upgrade
                try {
                    $stripeResult = $user->subscription('main')->swap($plan['stripe_plan_id']);
                } catch (\Stripe\Error\Card $e) {
                    $body = $e->getJsonBody();
                    return ['result' => false, 'message' => $body['error']['message']];
                }
                
                if (isset($stripeResult['stripe_id']) && !empty($stripeResult['stripe_id'])) {
                    $renewDate = date("Y-m-d 00:00:00", strtotime("-1 day", strtotime("+1 ".$plan->charge_freq)));

                    // automatically disable old plan and enable new plan
                    UserSubscription::where('user_id', $userID)->where('status', 1)->update(['status' => 0]);
                    UserSubscription::updateOrCreate(["user_id"=>$userID, 'stripe_id' => $stripeResult['stripe_id']], ["user_plan_id"=>$plan->plan_id, "subscription_id" => $stripeResult['stripe_id'], 'user_product_id' => $plan->id , "status"=>1, "subscription_renew_date"=>$renewDate]);
                  
                    // add or update user usage since the plan is getting changed
                    UserUsageCsv::updateOrCreate(["user_id"=> $userID], ["records_imported" => 0]);
                    UserUsageDocs::updateOrCreate(["user_id"=> $userID], ["docs_sent" => 0]);
                    UserUsageTasks::updateOrCreate(["user_id"=> $userID], ["tasks_used" => 0]);
                } else {
                    return ['result' => false, 'message' => 'Stripe API failed'];
                }
            } elseif ($targetPriority < $currentPriority) { //downgrade
                try {
                    //cancel current plan at the end of billing date
                    Stripe::setApiKey(getenv('STRIPE_SECRET'));
                    $subscription = \Stripe\Subscription::retrieve($exitsingSubscription->stripe_id);
                    $subscription->update($exitsingSubscription->stripe_id, ['cancel_at_period_end' => true]);
                    $gracePeriod = gmdate("Y-m-d", $subscription['current_period_end']);

                    UserSubscription::where('user_id', $userID)->where('status', 1)->update(['ends_at' => $gracePeriod ]);
                    //subscribe to new plan
                    $stripeResult = $user->newSubscription('main', $plan['stripe_plan_id'])->create();
                } catch (\Stripe\Error\Card $e) {
                    $body = $e->getJsonBody();
                    return ['result' => false, 'message' => $body['error']['message']];
                }
                
                if (isset($stripeResult['stripe_id']) && !empty($stripeResult['stripe_id'])) {
                    $renewDate = date("Y-m-d 00:00:00", strtotime("-1 day", strtotime("+1 ".$plan->charge_freq)));

                    // disable new plan (status = 0) to continue the benifits of higher old plan
                    // until the end of grace period (check and set via cron).
                    UserSubscription::updateOrCreate(["user_id"=>$userID, 'stripe_id' => $stripeResult['stripe_id']], ["user_plan_id"=>$plan->plan_id, "subscription_id" => $stripeResult['stripe_id'], 'user_product_id' => $plan->id , "status"=>2, "subscription_renew_date"=>$renewDate]);
                  
                    //add or update user usage since the plan is getting changed
                    UserUsageCsv::updateOrCreate(["user_id"=> $userID], ["records_imported" => 0]);
                    UserUsageDocs::updateOrCreate(["user_id"=> $userID], ["docs_sent" => 0]);
                    UserUsageTasks::updateOrCreate(["user_id"=> $userID], ["tasks_used" => 0]);
                } else {
                    return ['result' => false, 'message' => 'Stripe API failed'];
                }
            }
        }


        return ['result' => true, 'stripeResult' => $stripeResult];
    }


    /**
    * Check whether the user is subscribed to a PAID plan
    *
    * @return boolean true of subscribed, otherwise false
    */
    public function alreadySubscribedAnyPlan($id)
    {
        $plan = FusePlans::where('name', 'Free')->get()->first();
        if (UserSubscription::where('user_id', $id)->where('user_plan_id', '<>', $plan->id)->get()->first()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Calculates the prorated amount for the user to see discount
    * @param:
    *   $user (\App\User)- Authed user record
    *   $targetPlan (App\FusePlanProdcut) - The query result of selected plan
    *   $exitsingSubscription (App\UserSubscriptio )- The user's current plan
    *
    * @return integer The prorated amount
    */
    public function calculatProrateAmount($user, $targetPlan, $exitsingSubscription)
    {
        Stripe::setApiKey(getenv('STRIPE_SECRET'));
        $result = \Stripe\Invoice::all(['customer' => $user->stripe_id]);
        $prorationDate = time();
        $subscription = \Stripe\Subscription::retrieve($exitsingSubscription->stripe_id);
        $items = [
            [
                'id' => $subscription->items->data[0]->id,
                'plan' => $targetPlan->stripe_plan_id, # Switch to new plan
            ],
        ];

        $invoice = \Stripe\Invoice::upcoming([
            'customer' => $user->stripe_id,
            'subscription' => $exitsingSubscription->stripe_id,
            'subscription_items' => $items,
            'subscription_proration_date' => $prorationDate,
        ]);

        // Calculate the proration cost:
        $cost = 0;
        $currentProrations = [];
        foreach ($invoice->lines->data as $line) {
            if ($line->period->start == $prorationDate) {
                array_push($currentProrations, $line);
                $cost += $line->amount;
            }
        }

        return $cost / 100;
    }



    public function storeCardData(Request $request)
    {
        if (!$this->helper->isUserGuest()) {
            $user = $this->helper->getLoggedInUser();
            $user_id = $user->id;

            $FullName = $request->first_name.' '.$request->last_name;
            
            $cardData = [
                'CardType' => $request->card_type,
                'ContactId' => $user->infusionsoft_contact_id,
                'CardNumber' => $request->card_number,
                'ExpirationMonth' => $request->month,
                'ExpirationYear' => $request->year,
                'CVV2' => $request->cvv,
                'NameOnCard' => $FullName,
                'BillAddress1' => $user->address1,
                'BillCity' => $user->city,
                'BillAddress2' => $user->address2,
                'BillCountry' => $user->country,
                'BillState' => $user->phone,
                'BillZip' => $user->post_code,
                'FirstName' => $user->first_name,
                'LastName' => $user->last_name,
                'PhoneNumber' => $user->phone,
                'Email' => $user->email
            ];

            // Validate Credit Card and Save if Valid Card.
            $lastFour = (int)substr($request->card_number, -4);
            
            #process card in Stripe
            $stripe = new \App\Services\StripeServices();
            #find user stripe contact id
            $billing_data = \App\UserStripeBilling::where("user_id", $user_id)->first();
            if (isset($billing_data->stripe_id) && $billing_data->stripe_id != "") {
                $stripe_card_return = $stripe->processCard($billing_data, $cardData);

                if (isset($stripe_card_return["id"])) {
                    \App\UserStripeBilling::where(["id"=>$billing_data["id"]])->update(array("stripe_card_id"=>$stripe_card_return["id"], "card_brand"=>$stripe_card_return["brand"], "card_last_four"=>$stripe_card_return["last4"]));
                }
            }
            
            $userCard = $this->infusionSoftService->matchCreditCard($user->infusionsoft_contact_id, $lastFour, $request->year, $request->month);
            
            $cardID = '';
            if (is_array($userCard) && count($userCard) > 0) {
                $cardID = $userCard[0]['Id'];
                $response =  array( 'status' => 'failed', 'message' => 'You have already added this card.' );
            } else {
                $cardID = $this->infusionSoftService->addCreditCard($cardData);
                if (!$cardID) {
                    return array( 'status' => 'failed', 'message' => 'Credit card failed to update.' );
                } else {
                    $response = array( 'status' => 'failed', 'message' => 'Credit updated successfully.' );
                }
            }
            
            #find active subscriptions for this user and update the credit card
            $active_subs = UserSubscription::whereIn("status", array(1,2))->where("user_id", $user->id)->get();
            foreach ($active_subs as $ind_subs) {

                #find PayStatus for this recurring order
                $pay_status_res = $this->infusionSoftService->findSubscriptionPaidStatus($ind_subs->subscription_id);

                #charge the invoice if it is not already paid
                if ($pay_status_res["status"] == false) {
                    $this->infusionSoftService->chargeInvoice($pay_status_res["InvoiceId"], "", $cardID, false);
                }

                #update the subscriptions with the new credit card
                $this->infusionSoftService->updateSubsription($ind_subs->subscription_id, ["CC1"=>$cardID]);
            }
            return $response;
        }
        return array( 'status' => 'failed', 'message' => 'Somthing went wrong.' );
    }

    public function storeUserData(Request $request)
    {
        if (!Auth::guest()) {
            $user = Auth::user();

            if (User::where('email', $request->email)->first() && $request->email != $user->email) {
                return 'Email already taken.';
            }
            
            $user->email        = $request->email;
            
            $contactData = [
                'FirstName' => $request->first_name,
                'LastName'  => $request->last_name,
                'Email'     => $request->email,
                'Company'   => $request->company_name,
                'City'      => $request->city,
                'Phone1'    => $request->phone,
                'StreetAddress1'  => $request->address1,
                'StreetAddress2'  => $request->address2,
                'Country'   => $request->country,
                'PostalCode'=> $request->post_code
            ];

            // Update user information at infusion soft
            $result = $this->infusionSoftService->storeUserOnInfusionSoft($contactData);

            if ($result) {
                UserAddress::updateOrcreate(
                    [ 
                        'user_id' => $user->id, 
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'company_name' =>$request->company_name, 
                        'address1' => $request->address1, 
                        'address2' => $request->address2, 
                        'city' => $request->city, 
                        'country' => $request->country, 
                        'post_code' => $request->post_code, 
                        'phone' => $request->phone 
                    ]);
                $user->save();
                return 'true';
            }
        }
        return 'false';
    }

    public function store_user_detail($request)
    {

        $account = Accounts::firstOrCreate(['owner_id' => Auth::id()]);
 
        $user_detail = UserAddress::updateOrcreate(
            ['account_id' => $account->id],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'company_name' =>$request->company_name, 
                'address1' => $request->address1, 
                'address2' => $request->address2, 
                'city' => $request->city,
                'state' =>  $request->state,
                'country' => $request->country, 
                'post_code' => $request->postcode
            ]
        );

        return 'true';
    }
}
