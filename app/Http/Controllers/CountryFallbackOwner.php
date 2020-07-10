<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Http\Requests;
use App\CountryFallbackOwner as Model;

class CountryFallbackOwner extends ApiController
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function show($infs_account_id, Request $request)
    {
        $fallback_owner = Model::where('infs_account_id', $infs_account_id)->where('user_id', $this->user->id)->get();
        
        return $this->respondSuccessfulWithData('Fallback Owner has been saved.', $fallback_owner);
    }

    public function store(Request $request)
    {
        if ($request->fallback_owner_id <= 0) {
            return $this->respondUnprocessable('There is no Fallback Owner Selected.');
        }

        if ($request->infs_account_id <= 0) {
            return $this->respondUnprocessable('There is no Infusionsoft Account Selected.');
        }

        $fallback_owner = new Model;

        $fallback_owner->fallback_owner_id 		= $request->fallback_owner_id;

        $fallback_owner->fallback_owner_name 	= $request->owner_name[0]['FirstName'].' '.$request->owner_name[0]['LastName'];

        $fallback_owner->user_id 				= $this->user->id;

        $fallback_owner->infs_account_id 		= $request->infs_account_id;

        $fallback_owner->save();

        return $this->respondSuccessful('Fallback Owner has been saved.');
    }

    public function update($infs_account_id, Request $request)
    {
        $fallback_owner_update = Model::where('infs_account_id', $infs_account_id)->where('user_id', $this->user->id)
            ->update(['fallback_owner_name' => $request->owner_name[0]['FirstName'].' '.$request->owner_name[0]['LastName'], 'fallback_owner_id' => $request->fallback_owner_id]);

        return $this->respondSuccessful('Fallback Owner has been updated.');
    }
}
