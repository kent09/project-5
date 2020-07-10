<?php

namespace App\Http\Middleware;

use Closure;

class VerifySubscription
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
        if ($request->user() && ! $request->user()->subscribed('main')) {
            // This user is not a paying customer...
            return redirect(config('subdomains.account').config('session.domain').'/billing');
        }

        return $next($request);
    }
}
