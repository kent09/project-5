<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CountryBasedOwnerRequest;
use Auth;
use App\CountryOwner;
use App\CountryOwnerGroup;

class CountryBasedOwner extends ApiController
{
    protected $infusionsoft_service;

    public function __construct()
    {
        $this->auth_user = Auth::user();
    }

    public function handle(CountryBasedOwnerRequest $request)
    {

        // Check if existing
        $country_owner_found = CountryOwner::where('user_id', $this->auth_user->id)->where('infs_account_id', $request->infs_account_id)->where('infs_person_id', $request->owner_id)->get();

        if ($country_owner_found->count() > 0) {
            $country_owner_id = $country_owner_found[0]->id;

            if (count($request->country_id) > 0) {
                foreach ($request->country_id as $country_id) {

                    // Check if this will duplicate the data
                    $country_owner_group_found = CountryOwnerGroup::where('country_owner_id', $country_owner_id)->where('infs_country_id', $country_id)->get();

                    if ($country_owner_group_found->count() <= 0) {
                        $country_based_owner = new CountryOwnerGroup;

                        $country_based_owner->country_owner_id = $country_owner_id;

                        $country_based_owner->infs_country_id = $country_id;

                        $country_based_owner->status = 1;

                        $country_based_owner->save();
                    } else {
                        \Log::info('We cannot add this country_id - ' . $country_id . ' it will duplicate the existing one with this country_owner_id - ' . $country_owner_id);
                    }
                }
            }

            return $this->respondSuccessful('Country Based Owner Group has been updated.');
        } else {

            // Create new country owner
            $country_owner = new CountryOwner;

            $country_owner->user_id = $this->auth_user->id;

            $country_owner->infs_account_id = $request->infs_account_id;

            $country_owner->infs_person_id = $request->owner_id;

            $country_owner->status = 1;

            $country_owner->owner_name = $request->owner_name[0]['FirstName'] . ' ' . $request->owner_name[0]['LastName'];

            $country_owner->save();

            foreach ($request->country_id as $country_id) {
                $country_based_owner = new CountryOwnerGroup;

                $country_based_owner->country_owner_id = $country_owner->id;

                $country_based_owner->status = 1;

                $country_based_owner->infs_country_id = $country_id;

                $country_based_owner->save();
            }
        }

        return $this->respondSuccessful('New Country Based Owner has been successfully added.');
    }

    public function patch($id, CountryBasedOwnerRequest $request)
    {
        CountryOwnerGroup::where('country_owner_id', $id)->delete();

        $country_owner = CountryOwner::find($id);

        foreach ($request->country_id as $country_id) {
            $country_based_owner = new CountryOwnerGroup;

            $country_based_owner->country_owner_id = $country_owner->id;

            $country_based_owner->status = 1;

            $country_based_owner->infs_country_id = $country_id;

            $country_based_owner->save();
        }

        return $this->respondSuccessful('Country Based Owner has been successfully updated.');
    }
}
