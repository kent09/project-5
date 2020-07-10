<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\UserDocument;
use App\Http\Requests;
use App\UserProducts;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::users()->get();
        $userCount = count($users);
        
        $enterprise = $professional = $free = 0;
        foreach ($users as $user) {
            if (isset($user->userSubscription->plan->name) && $user->userSubscription->plan->name == 'Free') {
                $free++;
            }
            if (isset($user->userSubscription->plan->name) && $user->userSubscription->plan->name == 'Enterprise') {
                $enterprise++;
            }
            if (isset($user->userSubscription->plan->name) && $user->userSubscription->plan->name == 'Professional') {
                $professional++;
            }
        }
        
        $data['enterprise'] = $enterprise;
        $data['professional'] = $professional;
        $data['free'] = $free;
        return view('admin.home', compact('userCount', 'data'));
    }
}
