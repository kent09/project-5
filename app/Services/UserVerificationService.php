<?php

namespace App\Services;

use App\UserVerification;

class UserVerificationService
{
    /**
     * UserVerificationService constructor.
     * @param UserVerificationRepoistry $userVerificationRepoistry
     */
    public function __construct()
    {
    }
    
    public function save(array $data)
    {
        return UserVerification::create($data);
    }
    
    public function update($userId, array $data)
    {
        return UserVerification::where('user_id', $userId)->update($data);
    }
}
