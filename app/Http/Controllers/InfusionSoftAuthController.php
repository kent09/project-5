<?php
/**
 * Created by PhpStorm.
 * User: rohan
 * Date: 10/25/17
 * Time: 10:08 AM
 */

namespace App\Http\Controllers;

use App\Services\InfusionSoftService;
use App\User;
use Carbon\Carbon;
use Infusionsoft;
use App\UsersIsAccounts;
use App\DocsTagSettings;
use App\InfsAccount;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Infusionsoft\Token as InfusionsoftToken;
use FusedSoftware\Contracts\InfusionSoftContract;

use App\Logger\Logger;
use App\Logger\LogToFile;

/**
 * Class InfusionSoftAuthController
 * @package App\Http\Controllers
 */
class InfusionSoftAuthController extends Controller
{
    protected $infusionSoftService;
    protected $logger;

    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->infusionSoftService = $infusionSoftService;

        $this->logger = (new Logger(new LogToFile));
    }

    public function connectInfusionSoft()
    {
        $user = \Auth::user();
        $infusionsoft = app(InfusionSoftContract::class);
        
        if ($user->role_id == 1 && isset($user->usersIsAccounts)) {
            if ($user->role_id == 1) {
                $infusionsoftInstance = $infusionsoft
                    ->admin($user->usersIsAccounts->id);
            } else {
                $infusionsoftInstance = $infusionsoft
                    ->client($user->usersIsAccounts->id);
            }
        } else {
            $infusionsoftInstance = $infusionsoft->infusionsoft();
        }

        return redirect($infusionsoftInstance->getAuthorizationUrl());
    }

    public function redirect(Request $request)
    {
        if (InfsAccount::where('user_id', \Auth::id())->first()) {
            $this->infusionSoftService->requestAndStoreAccessTokens($request);
            return redirect(url('/manageaccounts'));
        }

        $this->infusionSoftService->requestAndStoreAccessTokens($request);
        $infusion_connect = true;

        return view('auth.connectAccount', compact('infusion_connect'));
    }

    public function saveContact($data = null)
    {
        $this->infusionSoftService->storeUserOnInfusionSoft();
    }

    public function getTagsFromISAccount(Request $request)
    {
        return $this->infusionSoftService->getTagsFromISAccount($request);
    }

    public function refreshTokenCron()
    {
        $response = array();
        $accounts = InfsAccount::where('active', 1)
            ->where('expire_date', '<=', Carbon::now()->subHours(1))
            ->where('error_reported', 0)
            ->get();

        foreach ($accounts as $account) {
            $infusionsoft = app(InfusionSoftContract::class)
                ->client($account->id);

            if ($account->user && $account->user->role_id == 1) {
                $this->logger->writeDown("({$account->name}) Admin's ClientId and ClientSecret was used");
            } else {
                $this->logger->writeDown("({$account->name}) Request Token Executed... UserID ({$account->user_id})");
            }

            try {
                $response = $infusionsoft->refreshAccessToken();
                $account->error_reported = 0;
                $account->save();
                $this->logger->writeDown("({$account->name}) Received New Refresh Token.");

            } catch (\Exception $exception) {
                $message = $this->getMessage($account, "Your infusionsoft token failed to refresh, for account $account->name.");
                $this->sendEmail(
                    ['webmaster@fusedsoftware.com','help@fusedsoftware.com'],
                    "FusedTools Token Refresh Failed, Account: " . $account->name,
                    $message
                );

                $account->error_reported = 1;
                $account->save();

                $this->logger->writeDown("({$account->name}) Failed to refresh token.");
                continue;
            }
            if (!empty($response)) {
                $expire_after       = $response->endOfLife;
                $expire_date        = date('Y-m-d H:i:s', $expire_after);

                $account->access_token = $response->accessToken;
                $account->referesh_token = $response->refreshToken;
                $account->expire_date = $expire_date;
                $account->error_reported = 0;
                $account->save();

                $this->logger->writeDown("({$account->name}) Infusionsoft Account has been updated.");
            }
        }
    }
    
    /**
     * Set the email content 
     * 
     * @param object $account
     * @param string $message
     * @return string
     */
    private function getMessage($account, $message)
    {
        $content  = "<h2>Hi ".$account->user->first_name." ".$account->user->last_name."</h2>";
        $content .= "<p>".$message."</p><br>";
        $content .= env('APP_ENV') !== 'production' ? 'This is from staging server' : '';
        return $content;
    }

    public function createCategory(Request $request)
    {
        $user = \Auth::user();
        try {
            $catName = $request->get('cat_name');
            $tempId = $request->get('temp_id');
            $accountId = $request->get('account_id');
            $type = $request->get('type');
            // $infsId = 6;
            $categoryId = $this->infusionSoftService->createCategory($catName, $accountId);
            $document_status = [];

            if ($type == 'pandadoc') {
                $document_status  = ['draft','sent','viewed','completed','voided','rejected'];
            } elseif ($type == 'docusign') {
                $document_status  = ['Sent','Delivered','Signed','Completed','Declined','Voided'];
            }

            foreach ($document_status as $status) {
                $tag_id = $this->infusionSoftService->createDocTags($categoryId, $catName . ' - ' . $status, $accountId);

                $checkIf = DocsTagSettings::where('user_id', $user->id)->where('infs_account_id', $accountId)->where('template_id', $tempId)->where('document_status', $status)->where('applied_tag_id', $tag_id)->first();

                if (empty($checkIf)) {
                    DocsTagSettings::create(['user_id' => $user->id,'infs_account_id' => $accountId, 'template_id' => $tempId, 'document_status' => $status, 'applied_tag_id' => $tag_id ]);
                }
            }
            return \GuzzleHttp\json_encode(['status' => true]);
        } catch (\Exception $e) {
            return \GuzzleHttp\json_encode(['status' => false]);
        }
    }
}