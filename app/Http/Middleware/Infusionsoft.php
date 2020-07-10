<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Infusionsoft
{
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Auth::user()) {
            $usersIsAccounts = \Auth::user()->usersIsAccounts;
            
            if (!$usersIsAccounts) {
                return redirect('showConnect');
            }
            return $next($request);
        } else {
            return \Redirect('login');
        }
    }
}
