<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Services\StripeServices;

class StripeController extends Controller
{
    public function __construct()
    {
    }

    public function fetchAllCustomer(StripeServices $stripe)
    {
        $stripe->fetchAllCustomer();
    }#end of function
}
