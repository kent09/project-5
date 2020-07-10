<?php

namespace App\Http\Controllers\Admin;

use App\User;
use App\Http\Requests;
use App\UserProducts;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class UsersController extends Controller
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
        return view('admin.users.index', compact('users'));
    }
    
    public function view($id)
    {
        $users = User::users()->get();
        return view('admin.users.index', compact('users'));
    }
    
    public function edit($id)
    {
        $users = User::users()->get();
        return view('admin.users.index', compact('users'));
    }
    
    public function update(Request $request)
    {
        $users = User::users()->get();
        return view('admin.users.index', compact('users'));
    }
    
    public function destroy($id)
    {
        $users = User::users()->get();
        return back()->with('success', 'User deleted successfully.');
    }
}
