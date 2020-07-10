<?php

namespace App\Repositories;

use App\Helpers\Helpers;
use App\InfsAccount;
use Carbon\Carbon;

class InfusionsoftAccountRepository extends Repository {


    const REPO_TYPE = 'infusionsoft_accounts';

    public function __construct() {
        $this->model = new InfsAccount;

        $this->repo_type = Helpers::REPO_CSV_IMPORT_RECORD;
    }

    /**
     * Get user's Infusionsoft Account which are not yet expired
     *
     * @param $userId
     * @return mixed
     */
    public function getActiveUserAccount($userId) {
        $infusionsoft_accounts = InfsAccount::where([
            [ 'user_id', '=', $userId ],
            [ 'expire_date', '>=', Carbon::now()]
        ])->get();

        return $infusionsoft_accounts;
    }

    /**
     * Get user's account details
     *
     * @param $userId
     * @return mixed
     */
    public function getUser($userId) {
        $infusionsoft_accounts = InfsAccount::where([
            [ 'user_id', '=', $userId ],
        ]);

        return $infusionsoft_accounts;
    }

}