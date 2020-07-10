<?php

namespace App\Http\Middleware;

use URL;
use Auth;
use Closure;
use App\User;
use App\InfsAccount;
use Illuminate\Http\Request;
use App\Services\InfusionSoftService;
use App\Services\UserService;
use Infusionsoft\Infusionsoft;
use App\Services\UserRoleService;

class VerifyINFSToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user_id = Auth::id();

        $account = InfsAccount::where('user_id', $user_id)->first();

        if ((!is_null($account->client_id) && !is_null($account->client_secret)) && (!empty($account->client_id) && !empty($account->client_secret))) {
            $credentials = array(
                'clientId'     => $account->client_id,
                'clientSecret' => $account->client_secret,
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            );
        } else {
            \Log::info('env '. env('INFUSIONSOFT_CLIENT_SECRET'));
            $credentials = array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            );
        }

        $infs_service = new Infusionsoft($credentials);

        \Log::info('Auth: ' . print_r(Auth::id(), true));

        try {
            $infs_service->data()->query("ContactGroup", 1000, 0, ['GroupName' =>'%'], ['Id','GroupName', 'GroupCategoryId'], 'Id', true);
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return redirect('/manageaccounts')->with('error', 'You need to re-authenticate your account.');
        }

        return $next($request);
    }
}
