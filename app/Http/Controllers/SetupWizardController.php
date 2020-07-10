<?php

namespace App\Http\Controllers;

use Auth;
use Mail;
use App\UserVerification;
use App\Services\SetupWizardService;
use App\Services\UserService;
use App\InfsAccount;
use App\DocsAccountsPanda;
use Illuminate\Http\Request;
use App\Services\InfusionSoftService;

use App\Http\Requests;

class SetupWizardController extends Controller
{
    //

    protected $setupWizardService;

    protected $userService;
    
    protected $infusionSoftService;

    public function __construct(SetupWizardService $service, UserService $userService, InfusionSoftService $infusionSoftService)
    {
        $this->setupWizardService = $service;
        $this->userService = $userService;
        $this->infusionSoftService = $infusionSoftService;
    }

    public function showEnterCodePage()
    {
        $user = \Auth::user();
        //Create contact in INFS
        $this->infusionSoftService->storeUserOnInfusionSoft();
        
        if ($user && $user->active == 1) {
            return redirect('/dashboard');
        }
        return view('auth.enterCodePage');
    }

    public function resendActivation()
    {
        $user = Auth::user();
        $userVer = UserVerification::where('user_id', $user->id)->first();
        $code = $userVer->code;

        Mail::send('emails.reminder', ['code' => $code, 'name' => $user->first_name], function ($m) use ($user) {
            $m->to($user->email)
                 ->from(env('MAIL_USERNAME'))
                 ->subject('Welcome To FusedTools');
        });

        return ['message' => 'success'];
    }

    public function verifyCode(Request $request)
    {
        if ($this->setupWizardService->verifyCode($request)) {
            
            // Applied tag for registered successfully
            //$this->infusionSoftService->applyTagUsigTagId($user->infusionsoft_contact_id,252);
            
            $this->userService->activateUser($request->userId);
            return \GuzzleHttp\json_encode(['status' => true]);
        } else {
            return \GuzzleHttp\json_encode(['status' => false]);
        }
    }

    public function renderConnectPage()
    {
        $infusion_connect = false;
        if (InfsAccount::where('user_id', \Auth::id())->first()) {
            $infusion_connect = true;
        }
        if ($infusion_connect) {
            return redirect('/');
        }
        return view('auth.connectAccount', compact(
            'infusion_connect'
        ));
    }
}
