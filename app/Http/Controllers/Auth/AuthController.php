<?php

namespace App\Http\Controllers\Auth;

use Mail;
use App\Services\UserVerificationService;
use App\User;
use App\UserSubscription;
use App\UserVerification;
use App\UserAddress;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Auth;
use App\Traits\SendEmail;
use App\Services\UserRoleService;
use App\Services\PlanService;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    use SendEmail;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/showCode';

    /**
     * @var UserVerificationService
     */
    protected $userVerficationService;

    protected $userRoleService;

    protected $planService;

    /**
     * Create a new authentication controller instance.
     *
     * @param UserVerificationService $userVerficationService
     */
    public function __construct(UserVerificationService $userVerficationService, UserRoleService $userRoleService, PlanService $planService)
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
        $this->userVerficationService = $userVerficationService;
        $this->userRoleService = $userRoleService;
        $this->planService = $planService;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:16',
            'last_name' => 'required|max:16',
            'company_name' => 'required|max:64',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'numeric'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $digits = 5;
        $code =  rand(pow(10, $digits-1), pow(10, $digits)-1);

        $user = User::create([
           'email' => $data['email'],
           'password' => bcrypt($data['password']),
           'company_name' => $data['company_name'],
           'first_name' => $data['first_name'],
           'last_name' => $data['last_name'],
           'role_id'   => $this->userRoleService->getUserRole()->id,
           'timezone'   => $data['timezone']
        ]);

        $fuseKey = substr(md5($user->id), 0, 10);
        
        foreach (range(0, 10) as $try) {
            $fuseKey = \CommanHelper::genrateUniqueId($user->id, $try);
            $fuseUser = User::where('FuseKey', $fuseKey)->get();
            if (count($fuseUser) == 0 || empty($fuseUser)) {
                $user->FuseKey = $fuseKey;
                $user->save();
                break;
            }
        }
        
        $useraddress =  new UserAddress();
        $useraddress->user_id = $user->id;
        $useraddress->first_name = $data['first_name'];
        $useraddress->last_name = $data['last_name'];
        $useraddress->company_name = $data['company_name'];
        $useraddress->country = $data['country'];
        $useraddress->phone = $data['phone'];
        $useraddress->save();
            
        $usub = new UserSubscription();
        $usub->user_id = $user->id;
        $usub->user_plan_id = $this->planService->getFreePlan()->id;
        $usub->status = 0;
        $usub->save();

        $userVerification = $this->userVerficationService->save([
            'code' => $code,
            'user_id' => $user->id
        ]);
        
        Mail::send('emails.reminder', ['code' => $code, 'name' => $data['first_name']], function ($m) use ($user) {
            $m->to($user->email)
                 ->from(env('MAIL_USERNAME'))
                 ->subject('Welcome To FusedTools');
        });

        return $user;
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
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $lockedOut = $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        
        
        $credentials = $this->getCredentials($request);
        $credentials['active']  = 1;
        $inactiveUser = User::where('email', $credentials['email'])->where('active', 0)->first();
        
        if ($inactiveUser) {
            $digits = 5;
            $code =  rand(pow(10, $digits-1), pow(10, $digits)-1);
            
            $userVerification = $this->userVerficationService->update($inactiveUser->id, [
                'code' => $code,
                'user_id' => $inactiveUser->id
            ]);
            \Auth::guard($this->getGuard())->attempt($credentials);
            

            Mail::send('emails.reminder', ['code' => $code, 'name' => $inactiveUser->first_name], function ($m) use ($inactiveUser) {
                $m->to($inactiveUser->email)
                     ->from(env('MAIL_USERNAME'))
                     ->subject('Welcome To FusedTools');
            });

            return redirect('/showCode')->with('user_id', $inactiveUser->id);
        }
        
        if (Auth::guard($this->getGuard())->attempt($credentials, $request->has('remember'))) {
            return $this->handleUserWasAuthenticated($request, $throttles);
        }
        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles && ! $lockedOut) {
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
