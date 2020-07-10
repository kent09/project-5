<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InfusionSoftService;
use App\Services\UserService;
use App\UserSubscription;
use App\UserUsage;
use App\UserUsageDocs;
use App\UserUsageCsv;
use App\UserUsageTasks;
use App\FuseInfsProducts;

use App\Http\Requests;

class BillingUsageController extends Controller
{
    protected $infusionSoftService;

    protected $userService;

    public function __construct(InfusionSoftService $infusionSoftService, UserService $userService)
    {
        $this->infusionSoftService = $infusionSoftService;
        $this->userService = $userService;
    }
    
    public function monitorSubscriptions()
    {

        //fetch all subscriptions for this user
        $exitsing_subscriptions = UserSubscription::whereIn("status", array(1,2))->where("subscription_renew_date", "<", date("Y-m-d"))->get();
        $today = date("Y-m-d");
        $date_7_days = date("Y-m-d", strtotime("-7 days"));
        foreach ($exitsing_subscriptions as $subscriptions) {
            // added to fix the issues with the subscriptions
            $userID = $subscriptions->user_id;

            //find the the invoice is paid or not in INFS
            $inv_paid_result = $this->infusionSoftService->findSubscriptionPaidStatus($subscriptions->subscription_id);
            $inv_paid_status = $inv_paid_result["status"];
            $next_pay_date = $inv_paid_result["NextBillDate"];

            if ($inv_paid_status == true) {
                UserSubscription::where("user_id", $userID)->update(["status"=>1, "subscription_renew_date"=>$next_pay_date]);
            }
            //if subscription renew date is less than 7 days from today, then cancel those subscriptions
            elseif ($inv_paid_status == false && $subscriptions->subscription_renew_date < $date_7_days) {
                $this->infusionSoftService->updateDataInInfusionSoft('RecurringOrder', $subscriptions->subscription_id, [
                'Status' => 'Inactive',
                'EndDate' => Carbon::now(),
                'AutoCharge' => 0,
                'ReasonStopped' => "User Plan Cancelled on $today due to non payment of the bill"
                ]);
                UserSubscription::where("user_id", $userID)->update(["status"=>3]);
            } elseif ($inv_paid_status == false && $subscriptions->subscription_renew_date >= $date_7_days) {
                UserSubscription::where("user_id", $userID)->update(["status"=>2]);
            }
        }
    }
    
    public function resetAllowance()
    {
        $today = date("Y-m-d");

        $array   = [];
        #fetch User ids whose reset date is today
        $array[] = UserUsageCsv::where('reset_date', $today)->pluck('user_id');
        $array[] = UserUsageDocs::where('reset_date', $today)->pluck('user_id');
        $array[] = UserUsageTasks::where('reset_date', $today)->pluck('user_id');

        $user_ids = [];

        foreach ($array as $user_id) {
            if (is_array($user_id) && count($user_id) > 0) {
                $user_ids = array_merge($user_ids, $user_id);
            }
        }

        if (count($user_ids) == 0) {
            return;
        }

        //fetch all subscriptions for this user
        $exitsing_subscriptions = UserSubscription::whereIn("status", array(1,2))->whereNotIn("user_plan_id", [3])->whereIn("user_id", $user_ids)->get();
        foreach ($exitsing_subscriptions as $subscriptions) {
            // added to fix the issues with the subscriptions
            $userID = $subscriptions->user_id;

            $reset_date = date("Y-m-d", strtotime("+1 month", strtotime($subscriptions->reset_date)));
            //UserUsage::updateOrCreate(["user_id"=>$userID], ["docs_sent"=>0, "reset_date"=>$reset_date]);
            UserUsageCsv::updateOrCreate(["user_id"=> $userID], ["records_imported" => 0, "reset_date"=>$reset_date]);
            UserUsageDocs::updateOrCreate(["user_id"=> $userID], ["docs_sent" => 0, "reset_date"=>$reset_date]);
            UserUsageTasks::updateOrCreate(["user_id"=> $userID], ["tasks_used" => 0, "reset_date"=>$reset_date]);
        }
    }
}
