<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Services\UserRoleService;
use App\Services\PlanService;
use App\Services\UserVerificationService;
use Mail;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    protected function authenticated(Request $request, $user)
    {
        $host = request()->getHttpHost();
        $hostparts = explode('.', $host);

        if ( $hostparts[0] == config('subdomains.account') ) {
            return redirect('/billing');
        }

        return redirect('/dashboard');
    }
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/dashboard';

    /**
     * @var UserVerificationService
     */
    protected $userVerficationService;

    protected $userRoleService;

    protected $planService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserVerificationService $userVerficationService, UserRoleService $userRoleService, PlanService $planService)
    {
        $this->middleware('guest', ['except' => 'logout']);
        $this->userVerficationService = $userVerficationService;
        $this->userRoleService = $userRoleService;
        $this->planService = $planService;
    }



    /**
    * Handle a login request to the application.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        // $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        
        
        // $credentials = $this->credentials($request);
        // $credentials['active']  = 1;
        $credentials = $request->only('email', 'password');
        
        $inactiveUser = User::where('email', $request->email)->where('active', 0)->first();
        
        if ($inactiveUser) {
            $digits = 5;
            $code =  rand(pow(10, $digits-1), pow(10, $digits)-1);
            
            $userVerification = $this->userVerficationService->update($inactiveUser->id, [
                'code' => $code,
                'user_id' => $inactiveUser->id
            ]);

            // \Auth::guard($this->getGuard())->attempt($credentials);
            

            Mail::send('emails.reminder', ['code' => $code, 'name' => $inactiveUser->first_name], function ($m) use ($inactiveUser) {
                $m->to($inactiveUser->email)
                     ->from(env('MAIL_USERNAME'))
                     ->subject('Welcome To FusedTools');
            });

            return redirect('/showCode')->with('user_id', $inactiveUser->id);
        }
        
        if($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }


        // if (\Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
        //     return $this->handleUserWasAuthenticated($request);
        // }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if (! $lockedOut) {
            $this->incrementLoginAttempts($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    public function getUserDataArray($data)
    {
        return [
            'FirstName' => $data['first_name'],
            'LastName'  => $data['last_name'],
            'Email'     => $data['email'],
            'Company'   => $data['company_name'],
        ];
    }

}
