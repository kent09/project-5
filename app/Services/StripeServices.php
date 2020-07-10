<?php
namespace App\Services;

use App\UserPlan;
use App\InfsAccount;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StripeServices
{
    protected $stripe_api="";
    protected $stripService;

    public function __construct()
    {
        $this->stripe_api=env('STRIPE_SECRET');

        $this->stripService = \Stripe::make($this->stripe_api);
    }

    public function fetchAllCustomer()
    {
        $stripe = $this->stripService;
        $customers = $stripe->customers()->all();
        return $customers;
    }

    public function fetchCustomer($customer_id)
    {
        $stripe = $this->stripService;
        $customers = $stripe->customers()->find($customer_id);
        if (isset($customers["email"])) {
            return $customers["email"];
        }
        return "";
    }

    public function insertCustomer($data)
    {
        $stripe = $this->stripService;
        $customers = $stripe->customers()->create($data);
        return $customers["id"];
    }

    public function updateCustomer($customer_id, $data)
    {
        $stripe = $this->stripService;
        $customers = $stripe->customers()->update($customer_id, $data);
        return $customer_id;
    }

    public function fetchAllCard($customer_id)
    {
        $stripe = $this->stripService;
        $cards_all = $stripe->cards()->all($customer_id);
        return $cards_all;
    }

    public function fetchCard($customer_id, $card_id)
    {
        $stripe = $this->stripService;
        $card = $stripe->cards()->find($customer_id, $card_id);
        return $card;
    }

    public function insertCard($customer_id, $data)
    {
        $stripe = $this->stripService;
        $token = $stripe->tokens()->create([
            'card' => $data,
        ]);
        $add_status = $stripe->cards()->create($customer_id, $token['id']);
        return $add_status;
    }

    public function updateCard($customer_id, $card_id, $data)
    {
        $stripe = $this->stripService;
        $add_status = $stripe->cards()->update($customer_id, $card_id, $data);
        return $add_status;
    }

    public function processCard($billing_data, $cardData)
    {
        $stripe_contact_id = $billing_data["stripe_id"];

        #fetch all card id for the contact, if the data existing
        $all_cards = $this->fetchAllCard($stripe_contact_id);

        #last 4 digit of the card
        if (isset($all_cards["data"]) && count($all_cards["data"]) > 0) {
            $lastFour = (int)substr($cardData["CardNumber"], -4);
            foreach ($all_cards["data"] as $single_card) {
                if ($single_card["last4"] == $lastFour) {
                    $data = array("exp_month"=>$cardData["ExpirationMonth"], "exp_year"=>$cardData["ExpirationYear"]);
                    $this->updateCard($stripe_contact_id, $single_card["id"], $data);
                    return $single_card;
                }
            }#end of loop
        }#end of all_cards count
        
        #process card into stripe
        $data = array("number"=>$cardData["CardNumber"], "exp_month"=>$cardData["ExpirationMonth"], "exp_year"=>$cardData["ExpirationYear"], "cvc"=>$cardData["CVV2"]);
        return $this->insertCard($stripe_contact_id, $data);
    }#end of function
}
