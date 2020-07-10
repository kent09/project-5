<?php
namespace App\Services;

use App\FuseRole;

class UserRoleService
{
    /**
     * UserService constructor.
     * @param UsersRoleRepositry $repoistry
     */
    public function __construct()
    {
    }

    public function getAdminRoleId()
    {
        return FuseRole::where('name', 'Superadmin')->first();
    }

    public function getUserRole()
    {
        return FuseRole::where('name', 'User')->first();
    }
}
