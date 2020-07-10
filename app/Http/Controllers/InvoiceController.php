<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
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

    public function redirectdashboard()
    {
        session(['app_type' => 'invoices']);
        return redirect('/dashboard');
    }

    public function index()
    {
        return view('invoiceshome');
    }
}
