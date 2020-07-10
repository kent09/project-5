<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\InfsAccount;
use App\Http\Requests;
use App\Services\StripeServices;

class ScriptController extends Controller
{
    /** Constructor */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
    * Show the page that provides a guide to move
    * infusionsoft opportunities with tags, forms and other goals
    *
    * @return \Illuminate\Http\Response
    */
    public function moveOpportunities()
    {
        return view('scripts.moveOpportunities');
    }

    /**
    * Show the page that provides a guide to update
    * credit cards in infusionsoft
    *
    * @return \Illuminate\Http\Response
    */
    public function updateCreditCards()
    {
        return view('scripts.updateCreditCards');
    }

    /**
    * Show the page that provides a guide to increment or
    * decrement INFS field values
    *
    * @return \Illuminate\Http\Response
    */
    public function addToValues()
    {
        return view('scripts.addToValues');
    }

    /**
    * Show the page that provides a guide to add a
    * list of contacts, purhcased products and merge into a single column
    *
    * @return \Illuminate\Http\Response
    */
    public function namesFromOrders()
    {
        return view('scripts.namesFromOrders');
    }

    /**
    * Show the page that provides a guide to copy values from one field to another
    *
    * @return \Illuminate\Http\Response
    */
    public function copyValues()
    {
        return view('scripts.copyValues');
    }

    /**
    * Show the page that provides a guide to calculate and store a date
    *
    * @return \Illuminate\Http\Response
    */
    public function calculateDates()
    {
        return view('scripts.calculateDates');
    }


    /* GEO SCRIPTS */

    /**
    * Show the page that provides a guide to extract contacts
    * given the postcode and radius
    *
    * @return \Illuminate\Http\Response
    */
    public function postcodeBasedOwner()
    {
        return view('scripts.postcodeBasedOwner');
    }

    /**
    * Show the page that provides a guide to extract contacts
    * given the postcode and radius
    *
    * @return \Illuminate\Http\Response
    */
    public function countryBasedOwner()
    {
        $user_id = \Auth::user() ? \Auth::user()->id : 0;

        $default_infs_account = InfsAccount::where('user_id', $user_id)->where('is_default', 1)->first();
        return view('scripts.countryBasedOwner', compact('default_infs_account'));
    }

    /**
    * Show the page that assign a tag to the contacts given the
    * tag id and radius
    *
    * @return \Illuminate\Http\Response
    */
    public function postcodeContactTagging()
    {
        return view('scripts.postcodeContactTagging');
    }
}
