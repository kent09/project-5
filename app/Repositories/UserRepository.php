<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserRepository extends Repository {

    protected $user;

    public function __construct() {

        $this->model = new User;

        $this->repo_type = Helpers::REPO_USERS;

        $this->user = Auth::user();

    }

    /**
     * Get currently logged in user
     *
     * @return mixed
     */
    public static function getUser() {
        return Auth::user();
    }


}