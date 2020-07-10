<?php

namespace App\Http\Middleware;

use App\DocsAccountsPanda;
use Closure;
use Auth;
use Illuminate\Http\Request;

class PandaDoc
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
        $usersPandaAccounts = DocsAccountsPanda::where('user_id', \Auth::id())->first();

        if (!$usersPandaAccounts) {
            return redirect('showConnect');
        }

        return $next($request);
    }
}
