<?php
namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;
use Session;
use DB;
use Carbon\Carbon;
use App\Services\DocusignService;
use App\DocsAccountsDocsign;

class DocAuthDocusignController extends Controller
{
    private $authUser 	 = '';
    protected $docusignService ;
     
    public function __construct(DocusignService $docusignService)
    {
        $this->authUser = Auth::user();
        $this->docsignService = $docusignService;
    }
    
    public function manageDocusign()
    {
        return redirect($url);
    }
    
    public function connectDocusign()
    {
        $url = env('DOCUSIGN_AUTH_URL').'/oauth/auth?response_type=code&scope=signature&client_id='.env('DOCUSIGN_KEY').'&redirect_uri='.env('DOCUSIGN_REDIRECT_URI');
        return redirect($url);
    }
    
    public function authDocusignAccount(Request $request)
    {
        $params = $request->all();
        if (isset($params['code']) && !empty($params['code'])) {
            $post = "grant_type=authorization_code";
            $post .="&code=".urlencode($params['code']);
            
            $headers = [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic '.base64_encode(env('DOCUSIGN_KEY').':'.env('DOCUSIGN_SECRET_KEY'))
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env('DOCUSIGN_AUTH_URL')."/oauth/token");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            
            try {
                $response = curl_exec($ch);
            } catch (\Exception $exception) {
                return redirect('docs/manageaccounts')->with('error', 'Some problem occur during refreshing token Please try again latter.');
            }
            curl_close($ch);

            if (!$response) {
                return redirect('docs/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
            }
            $response_obj_arr = json_decode($response, true);
            
            if (isset($response_obj_arr['error'])) {
                return redirect('docs/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
            }
            
            $expireDate = Carbon::now()->addSeconds($response_obj_arr['expires_in']);
            DocsAccountsDocsign::create([ 'user_id' => $this->authUser->id, 'access_token' => $response_obj_arr['access_token'], 'refresh_token' => $response_obj_arr['refresh_token'], 'expires_date' => $expireDate ]);
            return redirect('docs/manageaccounts')->with('success', 'You have configured your account with fusedtools.');
        }
    }
    
    public function refreshDocusignAccount()
    {
        if (!isset($this->authUser->docuSign->refresh_token)) {
            return redirect('docs/manageaccounts')->with('error', 'Something went wrong. Please try after sometime');
        }
        
        $refreshToken = $this->authUser->docuSign->refresh_token;
        $post = "grant_type=refresh_token";
        $post .="&refresh_token=".urlencode($refreshToken);
        
        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic '.base64_encode(env('DOCUSIGN_KEY').':'.env('DOCUSIGN_SECRET_KEY'))
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('DOCUSIGN_AUTH_URL')."/oauth/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        try {
            $response = curl_exec($ch);
        } catch (\Exception $exception) {
            return redirect('docs/manageaccounts')->with('error', 'Some problem occur during refreshing token Please try again latter.');
        }
        curl_close($ch);
        
        if (!$response) {
            return redirect('docs/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        $response_obj_arr = json_decode($response, true);
        
        if (isset($response_obj_arr['error'])) {
            return redirect('docs/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        
        $expireDate = Carbon::now()->addSeconds($response_obj_arr['expires_in']);
        DocsAccountsDocsign::where('user_id', $this->authUser->id)->update([ 'access_token' => $response_obj_arr['access_token'], 'refresh_token' => $response_obj_arr['refresh_token'], 'expires_date' => $expireDate ]);
        return redirect('docs/manageaccounts')->with('success', 'You have reauth your account with fusedtools.');
    }
    
    public function refreshDocusignAccountCron()
    {
        $docuAccounts = DocsAccountsDocsign::get();
        
        $headers = [
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic '.base64_encode(env('DOCUSIGN_KEY').':'.env('DOCUSIGN_SECRET_KEY'))
            ];
            
        foreach ($docuAccounts as $account) {
            $post = "grant_type=refresh_token";
            $post .="&refresh_token=".urlencode($account->refresh_token);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, env('DOCUSIGN_AUTH_URL')."/oauth/token");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);  //Post Fields
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            try {
                $response = curl_exec($ch);
            } catch (\Exception $exception) {
                continue;
            }
            curl_close($ch);
            
            if (!$response) {
                continue;
            }
            
            $response_obj_arr = json_decode($response, true);
            
            if (isset($response_obj_arr['access_token'])) {
                $expireDate = Carbon::now()->addSeconds($response_obj_arr['expires_in']);
                DocsAccountsDocsign::where('refresh_token', $account->refresh_token)->update([ 'access_token' => $response_obj_arr['access_token'], 'refresh_token' => $response_obj_arr['refresh_token'], 'expires_date' => $expireDate ]);
            }
        }
    }

    public function deleteDocusign(Request $request)
    {
        DocsAccountsDocsign::where('user_id', \Auth::id())->first()->delete();
        return redirect('docs/manageaccounts')->with('success', 'Docusign integration removed.');
    }
}
