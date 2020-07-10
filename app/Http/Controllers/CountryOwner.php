<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InfusionSoftService;
use Auth;

use App\CountryOwner as Model;
use App\CountryOwnerGroup;
use App\InfsAccount;
use App\InfsCountry;

class CountryOwner extends ApiController
{
    protected $infusionsoft_service;

    public function __construct(InfusionSoftService $infusionsoft_service)
    {
        $this->auth_user = Auth::user();

        $this->infusionsoft_service = $infusionsoft_service;
    }

    public function index(Request $request)
    {
        $owners = $this->infusionsoft_service->getUsers($this->auth_user->id, $request->accountID);

        $countries = InfsCountry::all();

        return $this->respondSuccessfulWithData('Request Successful', ['owners' => $owners, 'countries' => $countries]);
    }

    public function getByUserInfusionsoftAccountId(Request $request)
    {
        $user_infs_account_found = InfsAccount::find($request->accountID);

        if (!$user_infs_account_found) {
            return $this->respondUnprocessable('User Infusionsoft Account not found.');
        }

        $user_id = $user_infs_account_found->user_id;

        $country_owners = Model::with([
            'country_owner_groups' => function ($query) {
                $query->with('country');
            },
            'user',
            'infusionsoft_account'])
         ->where('user_id', $user_id)
         ->get();

        if ($country_owners->count() > 0) {
            foreach ($country_owners as $country_owner) {
                $readable_countries = '';

                if (count($country_owner->country_owner_groups) > 0) {
                    $ctr_comma = 0;
                    foreach ($country_owner->country_owner_groups as $country_owner_group) {
                        $ctr_comma++;
                        $readable_countries .= $country_owner_group->country->country_name;
                        $readable_countries .= $ctr_comma < count($country_owner->country_owner_groups) ? ', ' : '';
                    }
                }

                $country_owner['readable_countries'] = $readable_countries;
            }
        }

        return $this->respondSuccessfulWithData('Request Successful.', $country_owners);
    }

    public function destroy($id)
    {
        CountryOwnerGroup::where('country_owner_id', $id)->delete();
        Model::where('id', $id)->delete();

        return $this->respondSuccessful('Owner Based Group has been deleted.');
    }
}
