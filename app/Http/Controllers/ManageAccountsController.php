<?php
namespace App\Http\Controllers;

use URL;
use App\Services\InfusionSoftService;
use App\Http\Requests;
use App\User;
use App\InfsAccount;
use App\DocsAccountsPanda;
use App\DocsAccountsDocsign;
use App\XeroAccounts;
use Illuminate\Http\Request;
use Infusionsoft\Token as InfusionsoftToken;
use Carbon\Carbon;
use FusedSoftware\Contracts\InfusionSoftContract;
use Infusionsoft;
use Session;
use DB;
use Auth;

class ManageAccountsController extends Controller
{
    private $authUser 	 = '';
    private $infusionsoft 	 = '';
    protected $infusionSoftService = '';
    
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        // $this->authUser = Auth::user();
        $this->infusionSoftService = $infusionSoftService;
    }


    public function listUsersAccount(Request $request)
    {
        $user = Auth::user();
        $accounts = InfsAccount::where('user_id', $user->id)->get();
        \Log::info('Account Found: ' . print_r($accounts, true));
        $pandadocConnect = false;
        $docusign = false;

        // adding Xero integration data
        $xero_accounts = XeroAccounts::where('user_id', \Auth::id())->get();
        if (DocsAccountsPanda::where('user_id', \Auth::id())->first()) {
            $pandadocConnect = true;
        }
        
        $docusign = DocsAccountsDocsign::where('user_id', $user->id)->first();
        return view('userAccounts.list_accounts', compact('accounts', 'pandadocConnect', 'docusign', 'xero_accounts'));
    }
    
    public function addUsersAccount()
    {

        if (Auth::user()->accountLimit()) {
            $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
            return redirect($this->infusionsoft->getAuthorizationUrl());
        } else {
            return redirect('/manageaccounts')->with('error', 'You have reached your acount limit.');
        }
    }
    
    public function saveUsersAccount(Request $request)
    {
        $response = array();
        $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
            'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ));
        //echo "<pre>"; print_r($request->all()); die;
        $code 	  = $request->code;
        if (!$code) {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        $response = $this->infusionsoft->requestAccessToken($code);
        if (!$response) {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }

        $userID 			= Auth::id();
        $startTime 			= date("Y-m-d h:i:s");
        $access_token 		= $response->accessToken;
        $referesh_token 	= $response->refreshToken;
        $expire_after 		= $response->endOfLife;
        $expire_date	  	= date('Y-m-d h:i:s', $expire_after);
        $token_type         = $response->extraInfo['token_type'];
        $scope         		= $response->extraInfo['scope'];
        $scope_arr			= explode("|", $scope);
        $account			= $scope_arr[1];
        if ($this->checkIfAccountExits($userID, $account) == true) {
            $update = array(
                    'access_token'	=>serialize($response),
                    'referesh_token'=>$referesh_token,
                    'expire_date' 	=> $expire_date
                );
            $account = $this->getIsAccount(\Auth::user()->id);
        } else {
            $create = array(
                    'user_id' 		=> $userID,
                    'access_token'	=>serialize($response),
                    'referesh_token'=>$referesh_token,
                    'expire_date' 	=> $expire_date,
                    'account' 		=> $account
                );
            $account = InfsAccount::create($create);
        }
        if ($account) {
            return redirect('/manageaccounts')->with('success', 'Account added successfully');
        } else {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
    }
    
    public function renameAccount(Request $request)
    {
        $params = $request->all();
        
        InfsAccount::where('id', $params['id'])->update(['name' => $params['name']]);
        
        return array('status'=>'success','message'=>'Name updated successfully.');
    }
    
    public function getAccountName(Request $request)
    {
        $params = $request->all();
        $InfsName = InfsAccount::where('id', $params['id'])->first();
        
        return array('status'=>'success','name'=>$InfsName->name);
    }
    
    public function deleteAccount(Request $request)
    {
        $response  			= array();
        $userID    			= Auth::id();
        $accountID 			= $request->accountID;
        $result   			= InfsAccount::where('user_id', $userID)->where('id', $accountID)->delete();
        if ($result) {
            $accounts 		= InfsAccount::where('user_id', $userID)->get();
            $response['view'] 	= view('userAccounts/list_accounts_ajax', compact('accounts'))->render();
            $response['msg'] = 'Account deleted successfully.';
            return $response;
        }
        $response['status'] = 'failed';
        $response['msg'] = 'Error occurs while completing the request. Please try after sometime.';
        return $response;
    }
    
    public function checkIfAccountExits($user_id, $account)
    {
        $result				= '';
        $result   			= InfsAccount::where('user_id', $user_id)->where('account', $account)->count();
        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function reauthAccount(Request $request)
    {
        $params = $request->all();
         
        $account = InfsAccount::where('user_id', Auth::id())->where('id', $params['accountID'])->first();
         
        if ($account) {
            $response = $this->infusionSoftService->getIfRefreshTokenHasExpired($account->id);
                    
            if (!empty($response)) {
                InfsAccount::where('id', $account->id)->update(['access_token' => $response['access_token'],'referesh_token' => $response['referesh_token'],'expire_date' => $response['expire_date']]);
                return response()->json(['status' => 'success', 'msg' => 'Account authentication successful.']);
            } else {
                return response()->json(['status' => 'failed', 'msg' => 'Account authentication failed.']);
            }
        }
        return response()->json(['status' => 'failed', 'msg' => 'Record not found.']);
    }

    public function grantNewPermission(InfusionSoftContract $infusionsoft)
    {
        $user = \Auth::user();
        $account = InfsAccount::where('user_id', $user->id)->first();
        $instance = $infusionsoft
            ->infusionsoft();

        if ($account->user->role_id == 1) {
            $instance = $infusionsoft
                ->admin($account->id);
        } else {
            if ($account->client_id && $account->client_id > 0 && $account->client_secret > 0) {
                $instance = $infusionsoft
                    ->client($account->id);
            }
        }
        return redirect($instance->getAuthorizationUrl());
    }

    public function getClientAndSecretId(Request $request)
    {
        $params = $request->all();
        $infs_account = InfsAccount::where('id', $params['id'])->first();
        
        return array('status'=>'success', 'client_id'=> $infs_account->client_id, 'client_secret' => $infs_account->client_secret);
    }

    public function addOwnClientIdAndSecret(Request $request)
    {
        $params = $request->all();

        if (strlen($params['client_id']) !== 24) {
            return array('status'=>'failed','msg'=>'Client ID is not valid.');
        }

        if (strlen($params['client_secret']) !== 10) {
            return array('status'=>'failed','msg'=>'Client Secret is not valid.');
        }
        
        $infs_account = InfsAccount::where('id', $params['id'])->first();
        if (!$infs_account || (array) count($infs_account) <= 0) {
            return array('status'=>'failed','message'=>'Infusionsoft Account not found on our system.');
        }
        
        $infs_account->client_id 		= $params['client_id'];
        $infs_account->client_secret 	= $params['client_secret'];
        $infs_account->save();

        return array('status'=>'success','message'=>'Name updated successfully.');
    }
}
