<?php
namespace App\Services;

use Illuminate\Http\Request;
use App\UserVerification;

class SetupWizardService
{
    public function __construct()
    {
    }

    public function verifyCode(Request $request)
    {
        $code = $request->get('code')."";
        
        $data = UserVerification::where('user_id', $request->userId)->first();
        
        if ($data != null && $code === $data->code) {
            if (!\Auth::user()) {
                \Auth::loginUsingId($request->userId, true);
            }
            return true;
        } else {
            return false;
        }
    }
}
