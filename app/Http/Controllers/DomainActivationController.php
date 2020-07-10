<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use Carbon\Carbon;
use App\Http\Requests;

class DomainActivationController extends Controller
{
    public function activateDomain(Request $request)
    {
        $user = User::Email($request->input('email'))->first();
        $domain = $request->input('request_url') ? parse_url($request->input('request_url'))['host'] : '';

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => sprintf("We couldn't find your FusedTools account what you like to do? \n\r
    			<a target='_blank' href='%s'>Try another email address</a> <a target='_blank' href='%s'>Create A Free Account</a>", $request->input('request_url'), url('/register'))
            ]);
        }
        
        if (empty($domain)) {
            return response()->json([ 'status' => false, 'message' => 'Activation failed, invalid domain.' ]);
        }

        /*// Disabled for now
        if( $user->domain_activated && $user->domain_activated != $domain ){
    		return response()->json([ 'status' => false, 'message' => sprintf('Activation failed, account already activated for %s.', $user->domain_activated ) ]);
    	}*/

        $user->update(['domain_activated' => $domain, 'activated_date' => Carbon::now() ]);
        return response()->json([ 'status' => true ]);
    }

    public function licenseCheck(Request $request)
    {
        $user = User::Email($request->input('email'))->first();
        $domain = $request->input('request_url') ? parse_url($request->input('request_url'))['host'] : '';

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => sprintf("We couldn't find your FusedTools account what you like to do? \n\r
    			<a target='_blank' href='%s'>Try another email address</a> <a target='_blank' href='%s'>Create A Free Account</a>", $request->input('request_url'), url('/register'))
            ]);
        }

        if (empty($domain)) {
            return response()->json([ 'status' => false, 'message' => 'License check failed, invalid domain.' ]);
        }

        /*// Disabled for now
        if( $user->domain_activated && $user->domain_activated != $domain ){
    		return response()->json([ 'status' => false, 'message' => sprintf('License check failed, account already activated for %s.', $user->domain_activated ) ]);
    	}*/

        return response()->json([ 'status' => true ]);
    }
}
