<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class UserInfusionsoftAccount extends Controller
{
    public function index()
    {
        $user_infs_accounts = [];
        
        if (count(\Auth::user()->infsAccounts) > 0) {
            $user_infs_accounts = \Auth::user()->infsAccounts;
        }

        $FuseKey = \Auth::user()->FuseKey;

        return ['user_infs_accounts' => $user_infs_accounts, 'FuseKey' => $FuseKey];
    }
}
