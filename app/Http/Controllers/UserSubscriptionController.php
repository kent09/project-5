<?php
namespace App\Http\Controllers;

use App\User;
use App\UserAddress;
use App\Http\Requests;
use App\FusePlans;
use App\FuseInfsProduct;
use App\FusePlanProduct;
use App\UserSubscription;
use App\Accounts;
use App\ToolFeatures;
use App\Plans;
use App\InfsCountry;
use App\AccountBilling;
use App\Helpers\Helpers;
use Laravel\Cashier\Cashier;
use App\Services\InfusionSoftService;
use App\Services\UserSubscriptionService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBillingDetailRequest;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Http\Requests\UpdateCardRequest;
use App\Http\Requests\ProcessOrderRequest; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Infusionsoft\Infusionsoft;
use Carbon\Carbon;
use Session;

class UserSubscriptionController extends Controller
{
    protected $userSubscriptionService;
    protected $infusionSoftService;

    /** Constructor */
    public function __construct(UserSubscriptionService $userSubscriptionService, InfusionSoftService $infusionSoftService)
    {
        $this->middleware('auth');
        $this->userSubscriptionService = $userSubscriptionService;
        $this->infusionSoftService = $infusionSoftService;
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
    }


    /**
    * Show the billing page that contains selection of plans and payment form V2.
    * Just a static Layout   
    * @return \Illuminate\Http\Response
    */

    public function index() {
        
        $user = Auth::user();

        $stripe_data = '';
        if($user->hasPaymentMethod()) {
            $stripe_data = $user->defaultPaymentMethod();
        }

        $subscriptions_plan = '';

        if($user->stripe_id) {
            $subscriptions_plan = \Stripe\Subscription::all(['customer' => $user->stripe_id]);
            $subscriptions_plan = $subscriptions_plan->data;
        }

        $invoices = $user->subscribed('main') ? $user->invoicesIncludingPending() : null;

        $plans = Plans::all();
        $countries = InfsCountry::all();
        $account = Accounts::where('owner_id', $user->id)->first();

        $subscription = '';
        if($user->subscribed('main')) {
            $subscription = $user->usersubscription->latest()->first();
        }

        $user_address = UserAddress::where('user_id', $user->id)->first();

        $intent = $user->createSetupIntent();

        if($account) {
            $user_address = UserAddress::where('account_id',$account->id)->first();
        }
        
        return view('v2.manageBilling.index', compact('user', 'plans', 'latest_payment_id','subscription', 'invoices', 'countries', 'subscriptions_plan', 'user_address', 'stripe_data', 'intent'));
    }


    /**
    * Process the submission of the payment form from index method.
    * Store the billing details to DB, store session and redirect to confirmation.
    *
    * @return redirect /billing/confirm
    */
    public function store(StoreBillingDetailRequest $request)
    {
        $user = Auth::user();
        
        $respond = $this->userSubscriptionService->store_user_detail($request);

        if($user->stripe_id) {
            $user->updateStripeCustomer(['name' => $request->first_name.' '.$request->last_name]);
        } else {
            $user->createAsStripeCustomer(['name' => $request->first_name.' '.$request->last_name]);
        }

        return redirect()->back()->with(Helpers::toastr('Billing Address is updated', 'success')); 
    }

 
    
    /**
    * Perform the actual payment transaction using Stripe API c/o laravel-cashier
    *
    * @return redirect /billing/success if the transaction succeed
    * @return redirect /billing/failed if the transaction failed
    */
    public function processOrder(ProcessOrderRequest $request) {
        
        $user = Auth::user();
        $account = Accounts::firstOrCreate(['owner_id' => $user->id]);
        $subscription = UserSubscription::where('account_id', $account->id)->first();
        $plan = Plans::find($request->planid);

        $this->userSubscriptionService->store_user_detail($request);
       
        if(!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }
        
        $add_payment_method = $this->userSubscriptionService->add_payment_method($request->paymentid);
        
        if($add_payment_method['status'] == 'error') {
            return redirect()->back()->with(Helpers::toastr($add_payment_method['msg'], 'error'));
        }

        $update_payment = $this->userSubscriptionService->update_payment($request, $user);

        if($update_payment['status'] == "error") {
            return redirect()->back()->with(Helpers::toastr($update_payment['msg'], 'error')); 
        }
        
        if(!$subscription) {

            $newSubscription = $this->userSubscriptionService->newSubscription($user, $plan, $account, $request);

            if($newSubscription['status'] == "error") {
                return redirect()->route(
                    'cashier.payment',
                    [$newSubscription['msg']->payment->id, 'redirect' => url('/billing')]
                );
            }

            return redirect()->back()->with(Helpers::toastr('You succcessful subscribe to '.$plan->label, 'success'));

        }

        if($user->subscribedToPlan($plan->stripe_sub_id, 'main')) {

            return redirect()->back()->with(Helpers::toastr('You already subscribe in this plan.' , 'error')); 

        } 

        $prev_plan = Plans::where('stripe_sub_id', $subscription->stripe_plan)->first();

        // Change plan
        try {
            
            $data = $user->subscription('main')->swapAndInvoice($plan->stripe_sub_id);

        } catch (IncompletePayment $exception) {

            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => url('/billing')]
            );

        }

        $retrieve_sub = $this->userSubscriptionService->retrieveSubscription($data);

        if($retrieve_sub['status'] == "error") {
            return redirect()->back()->with(Helpers::toastr($add_payment_method['msg'], 'error'));
        }

        $diff_token = $plan->monthly_token_amount - $prev_plan->monthly_token_amount;

        $subscription = UserSubscription::find($data->id);
        $subscription->user_plan_id = $plan->id;
        $subscription->prev_bill_date = Carbon::createFromTimestamp($retrieve_sub['msg']->current_period_start);
        $subscription->next_bill_date = Carbon::createFromTimestamp($retrieve_sub['msg']->current_period_end);
        
        // if customer upgrade subscription
        if($prev_plan->id < $plan->id) {
            $subscription->token_count = $subscription->token_count + abs($diff_token);
        }
        
        // if customer downgrade subscription
        if($prev_plan->id > $plan->id) {
            $subscription->token_count = $subscription->token_count - abs($diff_token);
        }

        $subscription->save();

        $this->userSubscriptionService->send_email($user, $plan);
    
        return redirect()->back()->with(Helpers::toastr('You have succcessful updated your subscription to '.$plan->label.' '.$plan->billing_period , 'success'));
    }

    /**
    * Responsible in handling submission after the user decided to change plan
    *
    * @return redirect /billing/confirm
    */
    public function changePlan(Request $request)
    {
        $user = Auth::user();

        //server-side validation
        if (!isset($user->stripe_id) || empty($user->stripe_id)) {
            return redirect('billing')->withErrors(['Customer ID not set!']);
        }

        $this->validate($request, [
            "Package" => "required|exists:fuse_plans_products,id"
        ]);

        $product = FusePlanProduct::find($request->Package);
        if (!$product) {
            return Redirect::back()->withErrors(['No such product ID!']);
        }
        
        \Session::put('product_id', $request->Package);
        \Session::put('change_plan', true);

        return redirect('/billing/confirm');
    }

    /**
    * Perform the actual payment transaction using Stripe API c/o laravel-cashier
    *
    * @return redirect /billing/success if the transaction succeed
    * @return redirect /billing/failed if the transaction failed
    */
    public function processChangePlan(Request $request)
    {
        $user = Auth::user();
        if (\Session::has('product_id') == false) {
            return redirect('/billing');
        }

        $plan = \Session::get('product_id');

        $paymentResult = $this->userSubscriptionService->subscribe($request, $plan);

        if ($paymentResult['result']) {
            return redirect('billing/success');
        }

        return redirect('billing/failed')->with('message', $paymentResult['message']);
    }

    /**
    * Show the success page after payment.
    *
    * @return \Illuminate\Http\Response
    */
    public function successOrder(Request $request)
    {
        if (Session::has('product_id') == false) {
            return redirect('/billing');
        }

        $plan = Session::get('product_id');
        $planProduct = FusePlanProduct::where('id', $plan)->first();

        \Session::forget('product_id');
        \Session::forget('stripeToken');
        \Session::forget('change_plan');

        return view('manageBilling.success', compact('planProduct'));
    }

    /**
    * Show the failed page after payment if transaction failed.
    *
    * @return \Illuminate\Http\Response
    */
    public function failedOrder(Request $request)
    {
        if (Session::has('product_id') == false) {
            return redirect('/billing');
        }

        \Session::forget('product_id');
        \Session::forget('stripeToken');
        \Session::forget('change_plan');

        return view('manageBilling.failed', compact('planProduct'));
    }

    /**
    * Update user billing address in stripe and DB
    *
    * @return redirect to billing with flash message
    */
    public function updateBillingAddress(Request $request)
    {
        $this->validate($request, $this->billigUpdateRule());

        $billingAddress = $this->buildBillingAddress($request);
        $user = Auth::user();
        UserAddress::updateOrcreate([ 'user_id' => $user->id ], $billingAddress);
        //TO DO: Add flash message
        return redirect('/billing')->with('message', "Billing details successfully updated.");
        ;
    }

    /**
    * Update user card info in stripe and DB
    * limited to last four digits in DB
    *
    * @return redirect to billing with flash message
    */
    public function updateCard(UpdateCardRequest $request)
    {

        $user = Auth::user();

        $account = Accounts::firstOrCreate(['owner_id' => $user->id]);

        $user_detail = UserAddress::where('user_id', $user->id)->first(); 

        if($account) {
            $user_detail = UserAddress::where('account_id', $account->id)->first();
        }
        if(!$user->stripe_id) {
            $user->createAsStripeCustomer();
        }

        $add_payment_method = $this->userSubscriptionService->add_payment_method($request->paymentid);
        
        if($add_payment_method['status'] == 'error') {
            return redirect()->back()->with(Helpers::toastr($add_payment_method['msg'], 'error'));
        }

        $user->updateDefaultPaymentMethod($request->paymentid);
    
        return redirect()->back()->with(Helpers::toastr('Your card is updated.' , 'success'));

    }

    /**
    * Cancels the subscription to the next billing cycle (grace period)
    * Benefits must still be usable until the grace period
    * Prioritize status 2
    *
    * @return redirect to billing with flash message
    */
    public function cancelSubscription()
    {
        //validate
        $user = Auth::user();
        $subscription = UserSubscription::where('user_id', $user->id)->where('stripe_status', 'active')->get();
        if(!$subscription) {
            return redirect()->back()->with(Helpers::toastr('You are not subscribe.' , 'error'));
        }

        if ($user->subscription('main')->onGracePeriod()) {
            return redirect()->back()->with(Helpers::toastr('You already cancel your subscription.' , 'error'));
        }

        $user->subscription('main')->cancel();


        //TO DO: Add flash message
        return redirect()->back()->with(Helpers::toastr('Your subscription is cancel.' , 'success'));
    }


    /* END)
    }
    /* END OF REFACTORE CODE */

    
    public function rebillPayment(Request $request)
    {
        $params = $request->all();
        $user = Auth::user();
        
        #function to validate if user's stripe billing data exists
        //$this->validateAndProcessUserInStripe($user->id, $user);
        
        $invoiceId = $params['invoiceId'];
        $cardId = $this->infusionSoftService->getCreditCard($user->infusionsoft_contact_id);
        
        if (!isset($cardId[0]['Id'])) {
            return array( 'Successful' => false, 'Message' => 'Card not found.'  );
        }
        
        $GetInvoiceId = $this->infusionSoftService->getInvoiceData($invoiceId, $user->infusionsoft_contact_id);
       
        if (!isset($GetInvoiceId[0]['Id'])) {
            return array( 'Successful' => false, 'Message' => 'Could not find the invoice.'  );
        }
        if (isset($GetInvoiceId[0]['PayStatus']) && $GetInvoiceId[0]['PayStatus'] != 0) {
            return array( 'Successful' => false, 'Message' => 'You have already paid this invoice.'  );
        }
        
        $cardId = $cardId[0]['Id'];
        $response = $this->infusionSoftService->chargeInvoice($invoiceId, "", $cardId);
        return $response;
    }


    /* PROTECTED FUNCTION STARTS HERE. DO NOT PUT PUBLIC FUNCTION BELOW */

    /**
    * Build an array of key-pair value where key is infusionsoft field name.
    *
    * @return array of infs contact fields with appropriate value.
    */
    protected function buildInfsContactField(Request $request, $user)
    {
        return [
            'FirstName' => $user->first_name,
            'LastName'  => $user->last_name,
            'Email'     => $user->email,
            'Company'   => $user->company_name,
            'City'      => $request->city,
            'Phone1'    => $request->phone,
            'StreetAddress1'  => $request->address1,
            'StreetAddress2'  => $request->address2,
            'Country'   => $request->country,
            'PostalCode'=> $request->post_code
        ];
    }

    /**
    * Build an array of credit card to be put on session (deprecated)
    * NOTE: Deprected, code will be removed in the future
    *
    * @return array of credit card.
    */
    protected function buildCreditCard(Request $request)
    {
        $FullName = $request->first_name.' '.$request->last_name;
        return [
            'NameOnCard' => $FullName,
            'BillAddress1' => $request->address1,
            'BillCity' => $request->city,
            'BillAddress2' => $request->address2,
            'BillCountry' => $request->country,
            'BillState' => $request->phone,
            'BillZip' => $request->post_code,
            'FirstName' => $request->first_name,
            'LastName' => $request->last_name,
            'PhoneNumber' => $request->phone,
            'Email' => $request->email
        ];
    }


    /**
    * Build an array of rules for server-side validation.
    *
    * @return array of laravel rules.
    */
    protected function billigUpdateRule()
    {
        return  [
              "first_name" => "required|alpha",
              "last_name" => "required|alpha",
              "company_name" => "required|string",
              "phone" => "required|numeric",
              "address1" => "required|string",
              "address2" => "string",
              "city" => "required|string",
              "country" => "required|string",
              "post_code" => "required|string",
              "state" => "required|string",
        ];
    }


    /**
    * Build an array of key-pair to be stored in user_address table
    *
    * @return array of laravel rules.
    */
    protected function buildBillingAddress(Request $request)
    {
        $email = str_replace(" ", "", $request->email);
        trim($email, ",");
        return [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company_name' =>$request->company_name,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'city' => $request->city,
            'country' => $request->country,
            'post_code' => $request->post_code,
            'phone' => $request->phone,
            'state' => $request->state,
            'email_list' => $email
        ];
    }

}
