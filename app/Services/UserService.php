<?php
namespace App\Services;

use Carbon\Carbon;
use App\User;

class UserService
{
    protected $userRoleService;
    
    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }
    
    public function activateUser($userId)
    {
        return User::updateOrCreate(['id' => $userId ], ['active' => 1 ]);
    }

    public function getAdmin()
    {
        return User::where('role_id', $this->userRoleService->getAdminRoleId()->id)->first();
    }

    public function getUserById($id)
    {
        return User::where('id', $id)->get()->first();
    }

    public function getInfusionContactId($id)
    {
        $data = $this->getUserById($id);
        return $data->infusionsoft_contact_id;
    }

    public function getUserByInfusionsoftContactId($contactId)
    {
        return User::where('infusionsoft_contact_id', $contactId)->get()->first();
    }
}
