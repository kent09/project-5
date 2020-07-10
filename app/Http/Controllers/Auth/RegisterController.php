<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Laravel\Cashier\Cashier;
use App\Services\UserRoleService;
use App\Services\PlanService;
use App\Services\UserVerificationService;
use App\UserSubscription;
use App\UserVerification;
use App\UserAddress;
use App\Accounts;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/showCode';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRoleService $userRoleService, PlanService $planService, UserVerificationService $userVerficationService)
    {
        $this->middleware('guest');
        $this->userRoleService = $userRoleService;
        $this->planService = $planService;
        $this->userVerficationService = $userVerficationService;
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
            'timezone'   => $data['timezone'],
            'trial_ends_at' => now()->addDays(14),
        ]);

        $account = new Accounts;
        $account->owner_id = $user->id;
        $account->account_name = $data['company_name'];
        $account->save();

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
        $useraddress->account_id = $account->id;
        $useraddress->first_name = $data['first_name'];
        $useraddress->last_name = $data['last_name'];
        $useraddress->company_name = $data['company_name'];
        $useraddress->country = $data['country'];
        $useraddress->phone = $data['phone'];
        $useraddress->save();

        $options = [
            'email' => $user->email,
            'name' => $useraddress->first_name.' '.$useraddress->last_name
        ];

        $user->createAsStripeCustomer($options);

        // $usub = new UserSubscription();
        // $usub->user_id = $user->id;
        // $usub->account_id = $account->id;
        // $usub->user_plan_id = $this->planService->getFreePlan()->id;
        // $usub->status = 0;
        // $usub->save();

        $userVerification = $this->userVerficationService->save([
            'code' => $code,
            'user_id' => $user->id
        ]);
        
        Mail::send('emails.reminder', ['code' => $code, 'name' => $data['first_name']], function ($m) use ($user) {
            $m->to($user->email)
                 ->subject('Welcome To FusedTools');
        });

        return $user;
    }
}
