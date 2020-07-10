<?php
namespace App\Services;

use App\DocsAccountsPanda;
use Illuminate\Http\Request;

class PandaDocService
{
    public function __construct()
    {
    }

    private function getPandaDocUrl()
    {
        return env('PANDADOC_URL');
    }

    private function getPandaDocClientId()
    {
        return env('PANDADOC_CLIENT_ID');
    }

    private function getPandaDocRedirectUri()
    {
        return env('PANDADOC_REDIRECT_URI');
    }

    private function getAuthUrl()
    {
        return $this->getPandaDocUrl().'?client_id='.$this->getPandaDocClientId().'&redirect_uri='.$this->getPandaDocRedirectUri().'&scope=read+write&response_type=code';
    }

    public function getAuthorizationUrl()
    {
        return $this->getAuthUrl();
    }
    
    public function getAccessTokenUrl()
    {
        return env('PANDADOC_ACCESS_TOKEN_URL');
    }

    public function transformAttributesOfAuthResponse($panda_response_array)
    {
        $data =  [
            'access_token' => $panda_response_array['access_token'],
            'refresh_token' => $panda_response_array['refresh_token'],
            'expire_date' => $panda_response_array['expire_date'],
            'user_id' => \Auth::id()
        ];
        if (!$this->checkIfPandaAccountExits(\Auth::id())) {
            $data['api_key'] = $this->randStrGen(20);
        }
        return $data;
    }

    private function checkIfPandaAccountExits($userID)
    {
        $response		= false;
        $accounts 		= DocsAccountsPanda::where('user_id', $userID)->get();

        if (count($accounts)) {
            $response  = true;
        }
        return $response;
    }

    public function randStrGen($len)
    {
        $result = "";
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789$$$$$$$1111111";
        $charArray = str_split($chars);
        for ($i = 0; $i < $len; $i++) {
            $randItem = array_rand($charArray);
            $result .= "".$charArray[$randItem];
        }
        return $result;
    }

    public function requestAndStoreAccessTokens(Request $request)
    {
        $auth_response		= $request->all();
        $code				= @$auth_response['code'];
        
        if ($code) {
            $response_array = $this->getAccessToken($code);
            $data  = $this->transformAttributesOfAuthResponse($response_array);
            
            $check = array_only($data, ['user_id', 'account']);
            DocsAccountsPanda::updateOrCreate($check, $data);
            //Todo: redirect to the home page
        }
    }

    private function getAccessToken($code)
    {
        $post_flds 			= array(
            'client_id' 	=>env('PANDADOC_CLIENT_ID'),
            'client_secret'	=>env('PANDADOC_CLIENT_SECRET'),
            'scope'			=>env('PANDADOC_SCOPE'),
            'grant_type' 	=>env('PANDADOC_GRANT_TYPE'),
            'code'			=>$code,
            'redirect_uri' 	=> env('PANDADOC_REDIRECT_URI')
        );
        $url = $this->getAccessTokenUrl();
        $headers = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($post_flds));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_flds);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        try {
            $response = curl_exec($ch);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors('Some problem occur during refreshing token Please try again latter.');
        }

        curl_close($ch);

        if (!$response) {
            return redirect('/manage-panda-account')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        $response_obj_arr = json_decode($response);

        if (isset($response_obj_arr->error)) {
            return redirect('/')->with('error', $response_obj_arr->error);
        }
        $response_array   = array(
            'access_token' =>$response_obj_arr->access_token,
            'refresh_token' =>$response_obj_arr->refresh_token,
            'expire_date' =>$response_obj_arr->expires_in,
        );
        return $response_array;
    }
}
