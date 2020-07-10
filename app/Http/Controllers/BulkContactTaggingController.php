<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class BulkContactTaggingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = \Auth::user();
        $accounts = $user->infsAccounts()->get();
        $count = 0;

        foreach ($accounts as $account) {
            $sync = $account->infsSync()->count();
            $count = $sync > 0 ? $count + $sync : $count;
        }

        return view('tag.bulkTag', compact('accounts', 'count'));
    }
}
