<?php

namespace App\Http\Controllers;

use App\User;
use App\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;

class UserController extends Controller
{
    private $user = array();

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Auth::user();
    }

    public function changePasswordView()
    {
        $user = $this->user;
        
        return view('auth.changePass', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);
        
        $params = $request->all();
        
        $user = User::where('id', $this->user->id)->first();
        if (empty($user)) {
            return back()->with('success', 'User not found.');
        }
        
        User::where('id', $this->user->id)->update([ 'password' => bcrypt($params['password']) ]);
        
        return back()->with('success', 'Password changed successfully.');
    }
    
    public function accountSetting()
    {
        return view('account-settings');
    }
    
    public function accountSettingUpdate(Request $request)
    {
        $emails = explode(',', $request->email_list);
        
        if (!empty($request->email_list)) {
            foreach ($emails as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return back()->with('error', 'Please enter valid email.');
                }
            }
        }
        
        UserAddress::where('user_id', $this->user->id)->update([ 'email_list' => json_encode($emails) ]);
        return back()->with('success', 'Settings updated.');
    }
}
