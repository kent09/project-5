<?php

namespace App\Http\Controllers;

use App\InfsAccount;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\CompanyContactMap;
use App\InfsSync;
use App\Services\InfusionSoftService;

class SyncController extends Controller
{

    /** Constructor */
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->middleware('auth');
    }

    /**
     * Show the page that will add fields to the company and
     * sync it with the contacts
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function companyContactSync(Request $request)
    {
        $user = \Auth::user();
        $accounts = $user->infsAccounts()->get();
        $count = 0;

        foreach ($accounts as $account) {
            $sync = $account->infsSync()->count();
            $count = $sync > 0 ? $count + $sync : $count;
        }

        return view('sync.companyContactSync', compact('accounts', 'count'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addField(Request $request)
    {
        $id = $request->input('account');
        $company_field = $request->input('cfield');
        $contact_field = $request->input('ctfield');

        $cc_mapping = new CompanyContactMap();
        $cc_mapping->infs_account_id = $id;
        $cc_mapping->company_field_map = $company_field;
        $cc_mapping->contact_field_map = $contact_field;
        $cc_mapping->save();

        $name = "Field successfully added!";

        $request->session()->flash('success', $name);
        return redirect()->back();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function editField(Request $request)
    {
        $cc_mapping = CompanyContactMap::find($request->input('id'));

        if (!$cc_mapping) {
            $request->session()->flash('danger', 'Mapping not found');
            return redirect()->back();
        }

        if ($request->input('cfield')) {
            $cc_mapping->company_field_map = $request->input('cfield');
        }

        if ($request->input('ctfield')) {
            $cc_mapping->contact_field_map = $request->input('ctfield');
        }

        $cc_mapping->update();

        $request->session()->flash('success', 'Field successfully updated!');
        return redirect()->back();
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteField(Request $request, $id)
    {
        $cc_mapping = CompanyContactMap::find($id);

        if (!$cc_mapping) {
            $request->session()->flash('danger', 'Mapping not found');
            return redirect()->back();
        }

        $cc_mapping->delete();

        $request->session()->flash('success', 'Field successfully deleted!');
        return redirect()->back();
    }

    /**
     * API for the Sync Config existence
     *
     * @param Request $request
     * @param $id
     * @return array
     */
    public function syncConfig(Request $request, $id)
    {
        $sync = InfsSync::where('infs_account_id', $id)->first();
        $count = 0;

        if (!$sync) {
            $sync = null;
        } else {
            $count = $sync->count();
            $sync = $sync->toArray();
        }

        return [
                'sync' => $sync,
                'status' => 'success',
                'count' => $count
        ];
    }
}
