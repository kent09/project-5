<?php
namespace App\Lib;

use App\InfsAccount;
use App\User;
use App\UserUsageCsv;
use App\UserUsageDocs;
use App\UserUsageTasks;
use App\UserSubscription;
use App\FuseInfsProducts;
use App\PostcCountries;
use App\UserAddress;
use App\Services\InfusionSoftService;
use Carbon\Carbon;

class CommanHelper
{
    private $authUser = array();
    
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->authUser = \Auth::user();
        $this->infusionSoftService = $infusionSoftService;
    }

    
    // Get infs token by logged in user
    public function userInfsToken()
    {
        $infsToken = InfsAccount::where('user_id', \Auth::user()->id)->first();
        return $infsToken;
    }
    
    // Get infs token by user id
    public function infsTokenByUserId($userId)
    {
        if (!empty($userId)) {
            $infsToken = InfsAccount::where('user_id', $userId)->first();
            return $infsToken;
        }
        return '';
    }
    
    // Get infs token by user id
    public function infsTokenByAccount($accountId)
    {
        if (!empty($accountId)) {
            $infsToken = InfsAccount::where('id', $accountId)->first();
            return $infsToken;
        }
        return '';
    }
    
    // check user limits
    public function userLimit($user, $plan, $account)
    {
        $data= array();
        $userRequest = UserUsageTasks::where('user_id', $user->id)->where('account', $account)->first();
        $requestCount = 0;
        if (isset($userRequest->tasks_used)) {
            $requestCount = $userRequest->tasks_used;
        }
        $data['requestCount'] = $requestCount;
        if ($requestCount >= $plan->monthly_task_limit) {
            $data['denied'] = 'denied';
        }
        return $data;
    }
    
    // Genrate affiliate id cookie
    public function genrateAffiliateCookie($request)
    {
        $params = $request->all();
        
        if (isset($params['ref']) && !empty($params['ref'])) {
            setcookie('referral_id', $params['ref'], time() + (86400 * 90), "/");
            return true;
        }
        return false;
    }
    
    // Genrate unique id
    public function genrateUniqueId($userId, $try)
    {
        return substr(md5($userId), $try, 10);
    }
    
    // Get country from country code
    public function getCountry($code)
    {
        $coutry = PostcCountries::where('country_code', $code)->first();
        return $coutry->country_name;
    }
    
    public function emailList()
    {
        $user = \Auth::user();
        $details = UserAddress::where('user_id', $user->id)->first();
        $emailList = json_decode($details->email_list);
        
        $list = '';
        if ($emailList) {
            $list = implode(',', $emailList);
        }
        return $list;
    }
    
    public function notifyEmails($userID = '')
    {
        if (!empty($userID)) {
            $user = User::where('id', $userID)->first();
        } else {
            $user = \Auth::user();
        }
        if (isset($user->id)) {
            $details = UserAddress::where('user_id', $user->id)->first();
            $emails = explode(",", $details->email_list);
            $emails[] = $user->email;
            
            if (isset($emails[0]) && $emails[0] != '') {
                $emails[] = $user->email;
                return $emails;
            } else {
                $emails[] = $user->email;
                return $emails;
            }
        }
        ;
    }
}
