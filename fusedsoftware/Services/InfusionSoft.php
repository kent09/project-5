<?php

namespace FusedSoftware\Services;

use URL;
use Auth;
use Request;
use App\InfsAccount;
use Carbon\Carbon;
use FusedSoftware\Contracts\InfusionSoftContract;
use FusedSoftware\Exceptions\InfusionCreateTokenException;
use FusedSoftware\Exceptions\InfusionRefreshTokenException;
use Infusionsoft as InfusionSoftPackage;
use Infusionsoft\Token;

class InfusionSoft implements InfusionSoftContract
{

    protected $userAuth;

    protected $adminInstance;

    protected $clientInstance;

    function __construct() {
        $this->userAuth = Auth::user();
    }

    /**
     * Create new instance for admin infusionsoft account
     *
     * @param integer $accountId
     * @return InfusionSoftPackage\InfusionSoft
     */
    public function admin($accountId)
    {
        $this->adminInstance = $this->newInstance(
            $accountId, $this->adminCredential()
        );

        return $this->adminInstance;
    }

    /**
     * Create new instance for client infusionsoft account
     *
     * @param integer $accountId
     * @return InfusionSoftPackage\InfusionSoft
     */
    public function client($accountId)
    {
        $this->clientInstance = $this->newInstance(
            $accountId, $this->clientCrendential()
        );

        return $this->clientInstance;
    }

    /**
     * Create new instance of infusionsoft for system call
     * 
     * @return InfusionSoftPackage\InfusionSoft
     */
    public function infusionsoft()
    {
        return $this->generateConfig(null, $this->clientCrendential());
    }

    /**
     * Create infusionsoft new instance
     *
     * @param integer $accountId
     * @param array $credentials
     *
     * @return InfusionSoftPackage\InfusionSoft
     */
    private function newInstance($accountId, $credentials)
    {
        $accessToken = InfsAccount::where('id', $accountId)
            ->first();

        $instance = $this->generateConfig($accessToken, $credentials);
        $token = $this->generateToken($accessToken);
        $instance->setToken($token);
        return $instance;
    }

    /**
     * Generate new instance of infusionsoft
     *
     * @param InfsAccount $infsAccount
     * @param array $credentials
     *
     * @return InfusionSoft
     */
    private function generateConfig($infsAccount, $credentials)
    {
        return new InfusionSoftPackage\InfusionSoft(array_merge($credentials, [
            'clientId' => isset($infsAccount->client_id) ? $infsAccount->client_id : $credentials['clientId'],
            'clientSecret' => isset($infsAccount->client_secret) ? $infsAccount->client_secret : $credentials['clientSecret'],
        ]));
    }

    /**
     * Generate Token
     *
     * @param object $accessToken
     * @return Infusionsoft\Token
     */
    private function generateToken($accessToken)
    {
        return new Token([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->referesh_token,
            'expires_in' => Carbon::parse($accessToken->expire_date)->timestamp,
            "token_type" => "bearer",
            "scope"=>"full|".$accessToken->account
        ]);
    }

    /**
     * Get the admin infusionsoft credentials
     *
     * @return array
     */
    private function adminCredential()
    {
        return [
            'clientId' => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
            'redirectUri' => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ];
    }

    /**
     * Get the client infusionsoft credentials
     *
     * @return array
     */
    private function clientCrendential()
    {
        return [
            'clientId' => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri' => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ];
    }


}