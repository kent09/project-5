<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\InfsAccount;
use Carbon\Carbon;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        
        $auth = Auth::user();
        $referral = \CommanHelper::genrateAffiliateCookie($request);

        if (!$auth) {
            return redirect('/login');
        }

        $accounts = InfsAccount::where('user_id', $auth->id)->first();
        if ($auth && $auth->active == 0) {
            return redirect('/showCode');
        }
        
        if ($accounts && $accounts->expire_date < Carbon::now()) {
            return redirect('/manageaccounts')->with('error', 'Please refresh your infusionsoft Key Token');
        }
        
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('register');
            }
        }

        return $next($request);
    }
}
