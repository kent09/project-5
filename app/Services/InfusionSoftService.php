<?php
namespace App\Services;

use URL;
use App\Helpers\CsvStatus;
use Illuminate\Support\Arr;
use App\UserPlan;
use App\InfsAccount;
use App\User;
use App\PostcTags;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Infusionsoft\Infusionsoft;
use Infusionsoft\Token as InfusionsoftToken;
use App\Traits\Retriable;

class InfusionSoftService
{
    use Retriable;

    protected $infusionsoftService;
    protected $userService;

    public function __construct(Infusionsoft $infusionsoft, UserService $userService)
    {
        $config = [
            'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ];


        $user = \Auth::user();
        if (isset($user->id)) {
            $account = InfsAccount::where('user_id', $user->id)->first();

            if ($account) {
                if (count((array) $account->client_id) > 0 && count((array) $account->client_secret) > 0) {
                    $config = array(
                        'clientId'     => $account->client_id,
                        'clientSecret' => $account->client_secret,
                        'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
                    );
                } else {
                    $config = array(
                        'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                        'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                        'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
                    );
                }
            }
        }

        $this->infusionsoftService = new Infusionsoft($config);

        $this->userService = $userService;
    }

    public function getAuthorizationUrl()
    {
        return $this->infusionsoftService->getAuthorizationUrl();
    }

    public function requestAccessToken($code)
    {
        $response = $this->infusionsoftService->requestAccessToken($code);

        if (!$response) {
            throw (new Exception('Infusionsoft access token request returned null'));
        }

        return $response;
    }

    public function getAccountNameByExtraInfoScope($scope)
    {
        //scope = full|rrs.infusionsoft.com
        return last(explode("|", $scope));
    }

    public function transformAttributesOfAuthResponse($tokenData)
    {
        \Log::info('Transform Attribute of Auth Response... Token Data:' . print_r($tokenData, true));

        $accountName = $this->getAccountNameByExtraInfoScope($tokenData->extraInfo['scope']);

        $expire_date = Carbon::createFromTimestamp($tokenData->endOfLife);

        return [
            'access_token' => $tokenData->accessToken,
            'referesh_token' => $tokenData->refreshToken,
            'expire_date' => $expire_date,
            'account' => $accountName
        ];
    }

    public function requestAndStoreAccessTokens(Request $request)
    {
        // $user = \Auth::user();
        // if( isset($user->id) ){
        //     $this->setAdminInfsToken($user->id);
        // }
        $accessToken = $this->requestAccessToken($request->code);
        $accesstokenData = $this->transformAttributesOfAuthResponse($accessToken);
        $accesstokenData['user_id'] = \Auth::id();
        $accesstokenData['name'] = strstr($accesstokenData['account'], '.', true);

        $check = Arr::only($accesstokenData, ['account', 'user_id']);
        $data = InfsAccount::updateOrCreate($check, $accesstokenData);
    }

    public function storeUserOnInfusionSoft($data = null)
    {
        $admin = $this->userService->getAdmin();
        $user = \Auth::user();

        if ($user && !$user->infusionsoft_contact_id) {
            $contactData = [
                'FirstName' => $user->first_name,
                'LastName' => $user->last_name,
                'Email' => $user->email,
                'Company' => $user->company_name
            ];

            try {
                $this->checkIfRefreshTokenHasExpired($admin->id);

                $contactId = $this->infusionsoftService->data()->query("Contact", 1, 0, ["Email" => $user->email], ['Id'], "Id", true);

                if (isset($contactId[0]['Id']) && !empty($contactId[0]['Id'])) {
                    $user->infusionsoft_contact_id = $contactId[0]['Id'];
                } else {
                    $user->infusionsoft_contact_id = $this->infusionsoftService->contacts('xml')->add($contactData);
                }

                // Applied tag for registered successfully
                //$this->applyTagUsigTagId($user->infusionsoft_contact_id,170);

                if (isset($_COOKIE['referral_id']) && !empty($_COOKIE['referral_id'])) {
                    $this->createAffiliate($user->infusionsoft_contact_id, $_COOKIE['referral_id']);
                    setcookie("referral_id", "", time() - 3600);
                }

                $this->optIn($user->email, 'Contact Was Opted In through the API');
            } catch (\Exception $exception) {
                InfsAccount::where('user_id', \Auth::id())->delete();
                return false;
            }
            $user->save();
        } else {
            try {
                $this->checkIfRefreshTokenHasExpired($admin->id);
                $this->infusionsoftService->contacts('xml')->update($user->infusionsoft_contact_id, $data);
                $this->optIn($user->email, 'Contact Was Opted In through the API');
            } catch (\Exception $exception) {
                return false;
            }
        }
        return true;
    }

    public function checkIfRefreshTokenHasExpired($id, $accountId = '')
    {
        $user = $this->userService->getUserById($id);

        if (!empty($accountId)) {
            $accessToken = \CommanHelper::infsTokenByAccount($accountId);
        } else {
            $accessToken = \CommanHelper::infsTokenByUserId($id);
        }

        $this->setAdminInfsToken($id);
        $this->setToken($accessToken);

        if (!$this->checkConnectionValidWithoutTokenRefresh($id)) {
            \Log::info('Refreshing Token...');

            $refreshToken = $this->handler(
                [$this->infusionsoftService, 'refreshAccessToken'],
                [],
                ['redirect' => 'manageaccount', 'error' => 'Infusionsoft account requires ReAuth.']
            );

            $refreshToken = $this->transformAttributesOfAuthResponse($refreshToken);
            $accessToken->update($refreshToken);

            $this->setToken($accessToken);
        }

        return false;
    }

    public function checkIfRefreshTokenHasExpired2($id, $accountId = '')
    {
        $user = $this->userService->getUserById($id);

        if (!empty($accountId)) {
            $accessToken = \CommanHelper::infsTokenByAccount($accountId);
        } else {
            $accessToken = \CommanHelper::infsTokenByUserId($id);
        }

        $this->setAdminInfsToken($id);
        $this->setToken($accessToken);

        if (!$this->checkConnectionValidWithoutTokenRefresh($id)) {
            \Log::info('Refreshing Token...');

            $refreshToken = $this->handler(
                [$this->infusionsoftService, 'refreshAccessToken'],
                [],
                ['redirect' => 'manageaccount', 'error' => 'Infusionsoft account requires ReAuth.']
            );

            $refreshToken = $this->transformAttributesOfAuthResponse($refreshToken);
            $accessToken->update($refreshToken);

            $this->setToken($accessToken);
        }

        return false;
    }

    /**
     *
     * Request another token if Refresh Token has expired
     *
     * @param integer | $id - This is Infs Account Id
     *
     * @return Response
     */
    public function getIfRefreshTokenHasExpired($id)
    {
        $accessToken = \CommanHelper::infsTokenByAccount($id);
        $userId = \Auth::user()->id;
        $this->setAdminInfsToken($userId);
        $this->setToken($accessToken);

        if (!$this->checkConnectionValidWithoutTokenRefresh($userId, $id)) {
            \Log::info('Checked Connection without Token Refresh');

            $refreshToken = $this->handler([$this->infusionsoftService, 'refreshAccessToken']);

            if ($refreshToken) {
                \Log::info('New Refresh Token:' . print_r($refreshToken, true));
                return $this->transformAttributesOfAuthResponse($refreshToken);
            }

            \Log::info('Failed to get a New Refresh Token.');

            return '';
        }
        return '';
    }

    public function setToken($accessToken)
    {
        $token = $this->makeInfusionTokenFromAccessToken($accessToken);
        $this->infusionsoftService->setToken($token);
    }

    public function makeInfusionTokenFromAccessToken($accessToken)
    {
        return new InfusionsoftToken([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->referesh_token,
            'expires_in' =>  Carbon::parse($accessToken->expire_date)->timestamp,
            "token_type" => "bearer",
            "scope"=>"full|".$accessToken->account
        ]);
    }

    public function createTags($userId, $accountId, $tagName)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        $tag_id = 0;
        if ($tagName == "") {
            return $tag_id;
        }

        $query = ["GroupName"=>$tagName];

        #check if the tag exist, else add
        $result = $this->infusionsoftService->data()->query("ContactGroup", 1, 0, $query, ["Id"], "Id", true);
        if (is_array($result) && count($result) > 0) {
            $tag_id = $result[0]["Id"];
        } else {
            $tag_id = $this->infusionsoftService->data()->add('ContactGroup', [
                'GroupName' => $tagName
            ]);
        }

        return $tag_id;
    }

    public function addCreditCard($creditCard)
    {
        $user = \Auth::user();
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        $result = $this->infusionsoftService->invoices()->validateCreditCard(
            $creditCard['CardType'],
            $creditCard['ContactId'],
            $creditCard['CardNumber'],
            $creditCard['ExpirationMonth'],
            $creditCard['ExpirationYear'],
            $creditCard['CVV2']
        );

        if ($result['Valid'] == 'true') {
            try {
                $creditCardId =  $this->infusionsoftService->data()->add('CreditCard', $creditCard);
                $this->checkIfRefreshTokenHasExpired($user->id);
                $this->infusionsoftService->data()->add('CreditCard', $creditCard);
                return $creditCardId;
            } catch (\Exception $exception) {
                return false;
            }
        }
        return false;
    }

    public function getCreditCard($contactID)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        try {
            $query = ["Status" => 3, "ContactId"=>$contactID];
            $returnFields = ["Id","ExpirationYear","Last4"];
            return $this->infusionsoftService->data()->query("CreditCard", 1, 0, $query, $returnFields, "Id", true);
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function matchCreditCard($contactID, $cardNumber, $year, $month)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        try {
            $query = ["Status" => 3, "ContactId"=>$contactID, "Last4" => $cardNumber, "ExpirationYear" => $year, "ExpirationMonth" => $month];
            $returnFields = ["Id","ExpirationYear","Last4"];
            return $this->infusionsoftService->data()->query("CreditCard", 1, 0, $query, $returnFields, "Id", true);
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function createInvoice($contactID, $creditCardID, $productID)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        $subscription = $this->getSpecificFiled('SubscriptionPlan', 1, 0, 'ProductId', $productID['id'], ['id','PlanPrice'])[0];
        try {
            $sub_id =  $this->infusionsoftService->invoices()->addRecurringOrder(
                $contactID,
                false,
                $subscription['id'],
                1,
                $subscription['PlanPrice'],
                false,
                3,
                $creditCardID,
                0,
                0
            );
            return $this->infusionsoftService->invoices()->createInvoiceForRecurring($sub_id);
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function getSpecificFiled($table, $limit, $page, $fieldName, $fieldValue, $returnFields)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        try {
            return $this->infusionsoftService->data()->query($table, $limit, $page, [$fieldName => $fieldValue], $returnFields, "Id", true);
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function chargeInvoice($invoiceID, $notes = "", $creditCardID, $bypassComissions = false)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        try {
            $payment_status = $this->infusionsoftService->invoices()->chargeInvoice($invoiceID, "", $creditCardID, 3, $bypassComissions);
            #code to check if the payment was really done or not
            if (!is_array($payment_status) || $payment_status == 0) {
                sleep(rand(5, 9));
                $amount_due = $this->infusionsoftService->invoices()->amtOwed($invoiceID);
                if ($amount_due == 0) {
                    $payment_status=array("Successful"=>1, "Code"=>"Approved");
                } else {
                    $payment_status=array("Successful"=>0, "Code"=>"ERROR");
                }
            }
            return $payment_status;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function checkConnectionValidWithoutTokenRefresh($id, $accountId='')
    {
        $user = $this->userService->getUserById($id);
        if (!empty($accountId)) {
            $accessToken = \CommanHelper::infsTokenByAccount($accountId);
        } else {
            $accessToken = \CommanHelper::infsTokenByUserId($id);
        }

        //$this->setToken($accessToken);

        try {
            $this->infusionsoftService->data()->query("ContactGroup", 1000, 0, ['GroupName' =>'%'], ['Id','GroupName', 'GroupCategoryId'], 'Id', true);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getTags($userId=null, $accountId=null)
    {
        if (is_null($userId) && is_null($accountId)) {
            $this->checkIfRefreshTokenHasExpired(\Auth::id());
        } else {
            $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        }

        return $this->infusionsoftService->data()->query("ContactGroup", 1000, 0, ['GroupName' =>'%'], ['Id','GroupName', 'GroupCategoryId'], 'Id', true);
    }

    public function checkISConnection()
    {
        $token = \CommanHelper::userInfsToken();
        if ($token->expire_date >= Carbon::now()) {
            return true;
        } else {
            return false;
        }
    }

    public function applyTagUsigTagId($contactId, $tagId)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        $result = $this->infusionsoftService->contacts('xml')->addToGroup($contactId, $tagId);
        return $result;
    }
    public function bulkAssignTag($userId, $accountId, $contactIds, $tagId, $table="", $field="", $rowid=0)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $con_service = $this->infusionsoftService->tags();
        $con_service->offsetSet("id", $tagId);

        $cur_total = 0;
        $db_update = false;
        if ($table != "" && $field !="" && $rowid > 0) {
            $table = new $table;
            $table_name = $table->getTable();
            if (\Schema::hasColumn($table_name, $field)) {
                $db_update = true;
            }
        }

        if (count($contactIds) > 100) {
            $tag_me = array();

            // Get the Total Contact IDs
            $total_contact_ids = count($contactIds);

            foreach ($contactIds as $cid) {
                $tag_me[] = $cid;
                if (count($tag_me) == 100) {
                    $result = $con_service->addContacts($tag_me);
                    $tag_me = array();

                    $cur_total = $cur_total + 100;
                    if ($db_update === true) {
                        $table::where("id", $rowid)->update([$field=>$cur_total]);
                    }

                    $this->failedIds($result, $tagId, $method = 'addContacts');
                }
            }
            if (count($tag_me) > 0) {
                $result = $con_service->addContacts($tag_me);

                $cur_total = $cur_total + count($tag_me);
                if ($db_update === true) {
                    $table::where("id", $rowid)->update([$field=>$cur_total]);
                }

                $this->failedIds($result, $tagId, $method = 'addContacts');
            }
        } else {
            $result = $con_service->addContacts($contactIds);

            $cur_total = $cur_total + count($contactIds);
            if ($db_update === true) {
                $table::where("id", $rowid)->update([$field=>$cur_total]);
            }

            $this->failedIds($result, $tagId, $method = 'addContacts');
        }
        return $result;
    }

    public function bulkRmvTag($userId, $accountId, $contactIds, $tagId, $table="", $field="", $rowid=0)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $con_service = $this->infusionsoftService->tags();
        $con_service->offsetSet("id", $tagId);

        $cur_total = count($contactIds);
        $db_update = false;
        if ($table != "" && $field !="" && $rowid > 0) {
            $table = new $table;
            $table_name = $table->getTable();
            if (\Schema::hasColumn($table_name, $field)) {
                $db_update = true;
            }
        }

        if (count($contactIds) > 100) {
            $tag_me = array();
            foreach ($contactIds as $cid) {
                $tag_me[] = $cid;
                if (count($tag_me) == 100) {
                    $result = $con_service->removeContacts($tag_me);
                    $tag_me = array();

                    $cur_total = $cur_total - 100;
                    if ($db_update === true) {
                        $table::where("id", $rowid)->update([$field=>$cur_total]);
                    }

                    $this->failedIds($result, $tagId, $method = 'removeContacts');
                }
            }
            if (count($tag_me) > 0) {
                $result = $con_service->removeContacts($tag_me);

                $cur_total = $cur_total - count($tag_me);
                if ($db_update === true) {
                    $table::where("id", $rowid)->update([$field=>$cur_total]);
                }

                $this->failedIds($result, $tagId, $method = 'removeContacts');
            }
        } else {
            $result = $con_service->removeContacts($contactIds);

            $cur_total = $cur_total - count($contactIds);
            if ($db_update === true) {
                $table::where("id", $rowid)->update([$field=>$cur_total]);
            }

            $this->failedIds($result, $tagId, $method = 'removeContacts');
        }
        return $result;
    }


    public function applyTagUserTagId($userId, $accountId, $contactId, $tagId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->contacts('xml')->addToGroup($contactId, $tagId);
        return $result;
    }

    public function removeTagUsigTagId($contactId, $tagId)
    {
        $result = $this->infusionsoftService->contacts('xml')->removeFromGroup($contactId, $tagId);
        return $result;
    }

    public function updateDataInInfusionSoft($table, $recordId, $data)
    {
        return $this->infusionsoftService->data()->update($table, $recordId, $data);
    }

    public function updateDataInInfusionSoftClient($userId, $accountId, $table, $recordId, $data)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        return $this->infusionsoftService->data()->update($table, $recordId, $data);
    }

    public function getMyRecurringEntryWithUserDefinedConditon($contactId, $condition)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        return $this->infusionsoftService->data()->query("RecurringOrder", 1000, 0, $condition, ['Id','ProductId', 'Status', 'ContactId', 'MerchantAccountId', 'BillingAmt', 'SubscriptionPlanId', 'NextBillDate', 'StartDate'], 'Id', true);
    }

    public function createSubscriptionAndBill($contactID, $creditCardID, $subscription_id, $qty, $price, $bill_status=false)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);

        $data = array();
        $sub_id = $this->infusionsoftService->invoices()->addRecurringOrder(
            $contactID,
            true,
            $subscription_id,
            (int)$qty,
            (double)$price,
            false,
            3,
            $creditCardID,
            0,
            0
        );

        //dd($sub_id);
        if ($bill_status && is_numeric($sub_id) && $sub_id > 0) {
            $invoiceId =  $this->infusionsoftService->invoices()->createInvoiceForRecurring($sub_id);
            $result = $this->chargeInvoice($invoiceId, "", $creditCardID, false);
            if ((is_bool($result) && $result == false) || (is_array($result) && in_array(strtolower($result["Code"]), array("declined", "error")))) {
                //since the cxharge was not successful, delete the subscription
                $this->infusionsoftService->invoices()->deleteInvoice($invoiceId);
                $subData = $this->infusionsoftService->invoices()->deleteSubscription($sub_id);
                $invoiceId = $sub_id = 0;
            }
        }
        $data['result'] = $result;
        $data['sub_id'] = $sub_id;
        $data['invoice_id'] = $invoiceId;
        return $data;
    }

    public function findSubscriptionPaidStatus($recurring_order_id, $recurring_only=false)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        $subs_result = $this->infusionsoftService->data()->query("RecurringOrder", 1, 0, ["Id"=>$recurring_order_id], ['Id', 'BillingAmt', 'SubscriptionPlanId', 'NextBillDate', 'StartDate'], 'Id', true);
        $return = ["status"=>true, "NextBillDate"=>""];
        foreach ($subs_result as $subs) {
            $return["NextBillDate"] = $subs["NextBillDate"]->format("Y-m-d");
            if ($recurring_only == true) {
                return $return;
            }

            $order_results = $this->fetchOrdersUsingRecurringId($subs['Id']);

            foreach ($order_results as $order) {
                $return["OrderId"] = $order["Id"];
                $inv_results = $this->fetchInvoiceUsingJobId($order['Id']);

                foreach ($inv_results as $inv) {
                    $return["InvoiceId"] = $inv["Id"];
                    if ($inv["PayStatus"] == 0) {
                        $return["status"] = false;
                        break;
                    }
                }
            }
        }
        return $return;
    }#end of function

    public function fetchOrdersUsingRecurringId($JobRecurringId)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        $order_results = $this->infusionsoftService->data()->query("Job", 1000, 0, ["JobRecurringId"=>$JobRecurringId], ["Id"], "Id", true);
        return $order_results;
    }

    public function fetchInvoiceUsingJobId($JobId)
    {
        $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
        $order_results = $this->infusionsoftService->data()->query("Invoice", 1000, 0, ["JobId"=>$JobId], ["Id", "PayStatus", "InvoiceTotal", "DateCreated"], "Id", true);
        return $order_results;
    }

    public function getMyOrderHistory($contactId)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        return $this->infusionsoftService->data()->query("Invoice", 1000, 0, ['ContactId' => $contactId], ['Id','TotalDue','TotalPaid','PayStatus','DateCreated'], 'Id', true);
    }

    public function getExtraFields($InvoiceId)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        $orderItemId = $this->infusionsoftService->data()->query("InvoiceItem", 1000, 0, ['InvoiceId' => $InvoiceId], ['OrderItemId'], 'Id', true);

        $response = array();
        if (isset($orderItemId[0]['OrderItemId'])) {
            $productId = $this->infusionsoftService->data()->query("OrderItem", 1000, 0, ['Id' => $orderItemId[0]['OrderItemId']], ['ProductId'], 'Id', true);
            if (isset($productId[0]['ProductId'])) {
                $response['product_id'] =  $productId[0]['ProductId'];
            }
        }

        $payStatus = $this->infusionsoftService->data()->query("InvoicePayment", 1000, 0, ['InvoiceId' => $InvoiceId], ['PayStatus'], 'Id', true);

        return $response;
    }


    public function updateSubsription($recurringOrderId, $data)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        return $this->infusionsoftService->data()->update('RecurringOrder', $recurringOrderId, $data);
    }#end of function

    public function fetchProductUsingId($product_id)
    {
        try {
            $this->checkIfRefreshTokenHasExpired($this->userService->getAdmin()->id);
            $infs_obj = $this->infusionsoftService->data();

            $product_result = $infs_obj->query("Product", 1, 0, ["Id"=>$product_id], ["Id","ProductName","ProductPrice"], "Id", false);
            if (is_array($product_result) && count($product_result) > 0) {
                return $product_result[0];
            }
        } catch (Exception $e) {
        }
        return [];
    }

    public function getInvoiceData($InvoiceId, $ContactId)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);
        return $this->infusionsoftService->data()->query("Invoice", 1000, 0, ['Id' => $InvoiceId,'ContactId' => $ContactId], ['Id','PayStatus'], 'Id', true);
    }#end of function

    public function createAffiliate($contactId, $referralId)
    {
        $admin = $this->userService->getAdmin();
        $this->checkIfRefreshTokenHasExpired($admin->id);

        $referral_data = array(
            'ContactId' => $contactId,
            'AffiliateId' => $referralId,
            'IPAddress' => \Request::ip(),
            'Type' => 1,
            'Source' => 'HTTP Post Referral'
        );

        try {
            $this->infusionsoftService->data()->add('Referral', $referral_data);
        } catch (Exception $e) {
        }
    }

    public function setAdminInfsToken($id)
    {
        $admin = User::where('id', $id)->first();
        if ($admin->role_id == 1) {
            $this->infusionsoftService = new Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        }
    }

    public function optIn($email, $optInReason)
    {
        try {
            // $this->infusionsoftService->emails()->optIn($email, $optInReason);

            return $this->infusionsoftService->emails('xml')->optIn($email, $optInReason);
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return $this->optIn($email, $optInReason);
        } catch (\Infusionsoft\Http\HttpException $no_record_found) {
            if (stripos($no_record_found->getMessage(), "An HTTP error occurred: Unauthorized") !== false) {
                return $this->optIn($email, $optInReason);
            }
        }
    }

    /**
     * Company Contact Sync
     *      - used if the method passed is company edit
     *
     * @param
     *      $userId - the user id of the client
     *      $accountId - infusion account id of the client
     *      $contactId - contact id being updated
     *      $data - the custom fields that will be updated for the contact
     * @return
     *      array - sucess / fail message depending on the api result
     */
    public function companyContactSync($userId, $accountId, $contactId, $data)
    {
        $result = null;
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        try {
            $result = $this->infusionsoftService->contacts('xml')->update($contactId, $data);
            \Log::info('Contact Update Result: '.json_encode($result));
        } catch (\Exception $e) {
            \Log::error('INFS Company Contact failed for ID: '.json_encode($contactId).' - reason :'.json_encode($e->getMessage()));
            return null;
        }

        return $result;
    }

    /**
     * Hook Creation Function
     *
     * @param $userId
     *        $accountId
     *        $event_type
     *        $url
     * @return \Infusionsoft\Api\Rest\RestModel|null
     */
    public function infusionsoftHookSubscription($userId, $accountId, $event_type, $url)
    {
        $result = null;
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        try {
            // initial number of event hook registered
            $cnt = 0;
            // get all the hooks created
            $hooks = $this->infusionsoftService->resthooks()->get()->toArray();
            \Log::info("Hook: ".json_encode($hooks));

            if (!empty($hooks)) {
                foreach ($hooks as $hook) {
                    if ($hook->eventKey == $event_type) {
                        // means the hook exists
                        $cnt++;
                    }
                }

                // if cnt == 0, then the hook is not yet created
                if ($cnt == 0) {
                    $result = $this->infusionsoftService->resthooks()->create([
                        'eventKey' => $event_type,
                        'hookUrl' => $url
                    ]);
                    \Log::info("Hook URL ".$url." Subscription result: ".print_r($result, true));
                }

                $hooks_updated = $this->infusionsoftService->resthooks()->get()->toArray();
                \Log::info("Hook Data Updated: ".json_encode($hooks_updated));
            }
            // if there are no hooks existing
            else {
                $result = $this->infusionsoftService->resthooks()->create([
                    'eventKey' => $event_type,
                    'hookUrl' => $url
                ]);
                \Log::info("Hook URL ".$url." Subscription result: ".print_r($result, true));
            }
        } catch (\Exception $e) {
            \Log::error('Failed to create a hook subscription: '.json_encode($accountId).' - reason :'.json_encode($e->getMessage()));
            return null;
        }

        return $result;
    }

    /**
     * Hook Deletion Function
     *
     * @param $userId
     *        $accountId
     *        $event_type
     *        $url
     * @return \Infusionsoft\Api\Rest\RestModel|null
     */
    public function infusionsoftHookDeleteSubscription($userId, $accountId, $event_type)
    {
        $result = null;
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        try {
            $hooks = $this->infusionsoftService->resthooks()->get()->toArray();
            \Log::info("Hook: ".json_encode($hooks));

            if (!empty($hooks)) {
                foreach ($hooks as $hook) {
                    if ($hook->eventKey == 'company.edit' || $hook->eventKey == 'company.add' || $hook->eventKey == 'contact.add') {
                        // set the primary of the resthook
                        $this->infusionsoftService->resthooks()->hookDelete($hook->key);
                        $result =  $this->infusionsoftService->resthooks()->hookDelete($hook->key);
                        \Log::info("Hook Deletion Subscription result: ".print_r($result, true));
                    }
                }
            }

            $hooks_updated = $this->infusionsoftService->resthooks()->get()->toArray();
            \Log::info("Hook Data Updated: ".json_encode($hooks_updated));
        } catch (\Exception $e) {
            \Log::error('Failed to delete a hook subscription: '.json_encode($accountId).' - reason :'.json_encode($e->getMessage()));
            return null;
        }

        return $result;
    }

    /**
     * Hook Auto verify and header settingz
     */
    public function hookAutoVerify()
    {
        $this->infusionsoftService->resthooks()->autoverify();
    }

    /**
     * Function that retrieves the custom field for the specific form
     *
     * @param $userId`
     *        $accountId
     *        $form
     * @return array|null
     */
    public function getInfsCustomField($userId, $accountId, $infs_table, $fields=null)
    {
        $result = null;
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        try {
            $data_service = $this->infusionsoftService->data();

            $table = "DataFormField";

            switch ($infs_table) {
                case "Contact": $FormId = -1; break;
                case "Opportunity": $FormId = -4; break;
                case "Company": $FormId = -6; break;
                case "Job": $FormId = -9; break;
                default: $FormId = -1;
            }
            $query = array("FormId"=>$FormId);

            if (is_null($fields)) {
                $fields = array("Values","Id","Label","Name","DataType");
            }

            $result = $data_service->query($table, 100, 0, $query, $fields, "Id", true);
            \Log::info("Get Custom Field result: ".print_r($result, true));
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error querying custom fields'.json_encode($accountId).' - reason :'.json_encode($e->getMessage()));
            return null;
        }

        return $result;
    }

    /**
     * Get COMPANY details
     *
     * @param
     *          $companyID
     *          $fields
     * @return array
     */
    public function getCompanyDetails($userId, $accountId, $companyID, $fields)
    {
        $result = null;
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        // update the company details
        try {
            $result = $this->infusionsoftService->data()->query("Company", 1, 0, ["Id"=>$companyID], $fields, "Id", true);
        } catch (\Exception $e) {
            \Log::error('Error getting the company details: '.json_encode($e->getMessage()));
        }

        return $result;
    }

    /**
     * Get CONTACT ID's related to a COMPANY
     *
     * @param
     *          $companyID
     * @return array
     */
    public function getCompanyContactIds($userId, $accountId, $companyID)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        return $this->infusionsoftService->data()->query("Contact", 100, 0, array("CompanyID"=>(int)$companyID), ['Id'], "Id", true);
    }



    public function stageMove($stageId, $contactId, $userId, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $stageResult = $this->infusionsoftService->data()->query('Stage', 1, 0, ['Id' => $stageId], array('Id','StageName'), "StageOrder", false);

        if (isset($stageResult[0]) && isset($stageResult[0]['Id'])) {
            $stageId =  $stageResult[0]['Id'];

            $query = array('ContactID' => $contactId);
            $return_fields = array('Id');
            $results = $this->infusionsoftService->data()->query('Lead', 1, 0, $query, $return_fields, "DateCreated", false);

            if (!isset($results[0]['Id'])) {
                $namequery = array('Id' => $contactId);
                $returnfields = array('FirstName','LastName');

                $contact_result = $this->infusionsoftService->data()->query('Contact', 1, 0, $namequery, $returnfields, 'Id', true);

                if (!isset($contact_result[0])) {
                    $output = array('response' => 'fail','message' => 'Could not retrieve contact details when making new opportunity.');
                    return $output;
                }

                $fname = $contact_result[0]['FirstName'];
                $lname = $contact_result[0]['LastName'];

                $name = $fname." ".$lname;

                $result = $this->infusionsoftService->data()->add('Lead', array(
                    'ContactID'               => $contactId,
                    'StageID'                 => $stageId,
                    'OpportunityTitle'        => $name
                ));

                $output = array('response' => 'success','message' => 'Opportunity Created & Stage Set.', 'result' => $result);
                return $output;
            } else {
                $recent_opp_id = $results[0]['Id'];

                $result = $this->infusionsoftService->data()->update('Lead', $recent_opp_id, array('StageID' => $stageId));

                $output = array('response' => 'success','message' => 'Opportunity Updated.', 'result' => $result);
                return $output;
            }
        } else {
            $output = array('response' => 'fail','message' => 'Stage not found.');
            return $output;
        }
    }

    public function productToField($contactId, $userId, $field, $method, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        if (strpos($field, '.') !== false) {
            $field = explode(".", $field);
        } else {
            $output = array('response' => 'fail','message' => 'Field name is not set correctly.');
            return $output;
        }

        $namequery = array('ContactId' => $contactId);
        $returnfields = array('Id');
        $order_result = $this->infusionsoftService->data()->query('Job', 1, 0, $namequery, $returnfields, 'DateCreated', false);

        if (isset($order_result[0]['Id'])) {
            $orderid = $order_result[0]['Id'];
        } else {
            $output = array('response' => 'fail','message' => 'Could not find any order.');
            return $output;
        }

        $namequery = array('OrderId' => $orderid);
        $returnfields = array('ItemName');

        $name_result = $this->infusionsoftService->data()->query('OrderItem', 1, 0, $namequery, $returnfields, 'Id', false);

        if (!isset($name_result[0]['ItemName'])) {
            $output = array('response' => 'fail','message' => 'No Items On Order.');
            return $output;
        }

        $items = "";

        foreach ($name_result as $name) {
            if ($items == "") {
                $items = $name['ItemName'];
            } else {
                $items .= ', '.$name['ItemName'];
            }
        }

        if ($method == 'append') {
            $namequery = array('Id' => $contactId);
            $returnfields = array($field[1]);
            $old = $this->infusionsoftService->data()->query('Contact', 1, 0, $namequery, $returnfields, 'Id', false);

            if (isset($old[0][$field[1]]) && $old[0][$field[1]] != "") {
                $items = $old[0][$field[1]].', '.$items;
            }
        }

        $result = $this->infusionsoftService->data()->update($field[0], $contactId, array($field[1] => $items));
        $output = array('response' => 'success','message' => 'Order Items Copied.', 'result' => $result);
        return $output;
    }

    public function copyFieldValue($userId, $contactId, $fieldfrom, $fieldto, $method, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        if (strpos($fieldfrom, '.') !== false && strpos($fieldto, '.') !== false) {
            $fieldfrom = explode(".", $fieldfrom);
            $fieldto = explode(".", $fieldto);
        } else {
            $output = array('response' => 'fail','message' => 'From OR To fields are not set correctly.');
            return $output;
        }

        if ($fieldfrom[0] == 'Contact') {
            $namequery = array('Id' => $contactId);
        } else {
            $namequery = array('ContactId' => $contactId);
        }
        $returnfields = array($fieldfrom[1]);
        $contact_result = $this->infusionsoftService->data()->query($fieldfrom[0], 1, 0, $namequery, $returnfields, 'Id', false);

        if (!isset($contact_result[0])) {
            $output = array('response' => 'fail','message' => 'Field not found.');
            return $output;
        }

        $value = "";

        if ($fieldto[0] == 'Contact') {
            $namequery = array('Id' => $contactId);
        } else {
            $namequery = array('ContactId' => $contactId);
        }

        $returnfields = array('Id',$fieldto[1]);

        $contact_result2 = $this->infusionsoftService->data()->query($fieldto[0], 1, 0, $namequery, $returnfields, 'Id', false);

        if (isset($contact_result2[0]['Id'])) {
            $queryid = $contact_result2[0]['Id'];
        } else {
            $output = array('response' => 'fail','message' => 'Could not detect ID of the new row.');
            return $output;
        }

        if ($method == 'append' && isset($contact_result[0][$fieldfrom[1]]) && $contact_result[0][$fieldfrom[1]] != "") {
            $value = $contact_result2[0][$fieldto[1]].", ";
        }

        $value .= $contact_result[0][$fieldfrom[1]];

        $result = $this->infusionsoftService->data()->update($fieldto[0], $queryid, array($fieldto[1] => $value));

        $output = array('response' => 'success','message' => 'Field copied.', 'result' => $result);
        return $output;
    }

    public function incrementFields($userId, $contactId, $field, $amount, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        if (strpos($field, '.') !== false) {
            $field = explode(".", $field);
        } else {
            $output = array('response' => 'fail','message' => 'Field name is not set correctly.');
            return $output;
        }

        if ($amount == "" || $amount == 0) {
            $output = array('response' => 'fail','message' => 'Amount increase blank or zero.');
            return $output;
        }

        if ($field[0] == 'Contact') {
            $namequery = array('Id' => $contactId);
        } else {
            $namequery = array('ContactId' => $contactId);
        }

        $returnfields = array('Id',$field[1]);
        $contact_result2 = $this->infusionsoftService->data()->query($field[0], 1, 0, $namequery, $returnfields, 'LastUpdated', false);

        if (isset($contact_result2[0]['Id'])) {
            $queryid = $contact_result2[0]['Id'];
        } else {
            $output = array('response' => 'fail','message' => 'Could not detect ID of the new row.');
            return $output;
        }

        if (!isset($contact_result2[0][$field[1]]) || $contact_result2[0][$field[1]] == "" || !isset($contact_result2[0][$field[1]])) {
            $fieldvalue = 0;
        } else {
            $fieldvalue = $contact_result2[0][$field[1]];
        }

        if (!is_numeric($fieldvalue)) {
            $output = array('response' => 'fail','message' => 'Field value is not numberic.');
            return $output;
        }

        $fieldvalue = $fieldvalue + $amount;

        $result = $this->infusionsoftService->data()->update($field[0], $queryid, array($field[1] => $fieldvalue));

        $output = array('response' => 'success','message' => 'Field incremented.', 'result' => $result );
        return $output;
    }

    public function updateCreditCard($userId, $contactId, $merchant, $accountId, $update_subs, $rebill_orders, $rebill_subs, $only_active)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $query = array('Status' => 3);
        $return = array('Id');
        $result = array();

        $active_cc_info = $this->infusionsoftService->data()->query('CreditCard', 1, 0, $query, $return, 'Id', false);

        if (!isset($active_cc_info[0]['Id'])) {
            $output = array('response' => 'fail','message' => 'Could not retrieve an active card.');
            return $output;
        }

        $active_cc_id = $active_cc_info[0]['Id'];

        if ($update_subs == 1 || $rebill_subs == 1) {
            if ($only_active == 1) {
                $query = array('Status' => 'Active');
            } elseif ($only_active == 0) {
                $query = array('Status' => '%');
            }

            $return = array('Id','CC1','Status');
            $recurring_orders = $this->infusionsoftService->data()->query('RecurringOrder', 1, 0, $query, $return, 'Id', false);

            //might need to add an exclusion here if they are rebilling orders as well later.
            if (empty($recurring_orders) && $rebill_orders != 1) {
                $output = array('response' => 'fail','message' => 'No subscriptions found to update or rebill.');
                return $output;
            }

            if (!empty($recurring_orders) && $rebill_subs == 1) {
                foreach ($recurring_orders as $r_orders) {
                    $query = array('RecurringId' => $r_orders['Id']);
                    $return = array('Id');

                    $results3 = $this->infusionsoftService->data()->query('JobRecurringInstance', 1000, 0, $query, $return, 'Id', false);

                    if (!empty($results3)) {
                        foreach ($results3 as $r3) {

                            //find the status on the invoices for those
                            $query = array('RecurringId' => $r3['Id']);
                            $return = array('Id','PayStatus');
                            $results4 = $this->infusionsoftService->data()->query('Invoice', 1000, 0, $query, $return, 'Id', false);

                            if (!empty($results3)) {
                                foreach ($results4 as $r4) {
                                    if (!isset($r4['PayStatus']) || $r4['PayStatus'] == 0) {
                                        //if they have a single unpaid invoice
                                        //Charge that unpaid invoice
                                        $result['cahrge_invoice'][] = $this->infusionsoftService->invoices()->chargeInvoice($r4['Id'], "Rebill", $active_cc_id, $merchant, true);
                                    }
                                }
                            }
                        }
                    }

                    if ($update_subs == 1 && $r_orders['CC1'] != $active_cc_id && $r_orders['Status'] == 'Active') {
                        $result['recurrinf_order'] = $this->infusionsoftService->data()->update('RecurringOrder', $r_orders['Id'], array('CC1'=> $active_cc_id,));
                    }
                }
            }
        }

        if ($rebill_orders == 1) {

            //find the status on the invoices for those
            $query = array('PayStatus' => '%');
            $return = array('Id','PayStatus');
            $results4 = $this->infusionsoftService->data()->query('Invoice', 1000, 0, $query, $return, "Id", true);

            if (!empty($results3)) {
                foreach ($results4 as $r4) {
                    if (!isset($r4['PayStatus']) || $r4['PayStatus'] == 0) {
                        //if they have a single unpaid invoice
                        //Charge that unpaid invoice
                        $result['rebill_order'] = $this->infusionsoftService->invoices()->chargeInvoice($r4['Id'], "Rebill", $active_cc_id, $merchant, true);
                    }
                }
            }
        }

        if (!empty($result['rebill_order']) || !empty($result['recurrinf_order']) || !empty($result['cahrge_invoice'])) {
            $output = array('response' => 'success','message' => 'Card updated.' , 'result' => $result );
        } else {
            $output = array('response' => 'success','message' => 'Card not updated.' , 'result' => $result );
        }

        return $output;
    }

    public function calculateDate($userId, $accountId, $contactId, $fieldto, $startdate, $add_time)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        if (strpos($fieldto, '.') !== false) {
            $fieldto = explode(".", $fieldto);
        } else {
            $output = array('response' => 'fail','message' => 'fieldto name is not set correctly.');
            return $output;
        }

        if ($fieldto[0] == 'Contact') {
            $namequery = array('Id' => $contactId);
        } else {
            $namequery = array('ContactId' => $contactId);
        }

        $returnfields = array('Id',$fieldto[1]);

        $contact_result2 = $this->infusionsoftService->data()->query($fieldto[0], 1, 0, $namequery, $returnfields, 'Id', false);

        if (isset($contact_result2[0]['Id'])) {
            $queryid = $contact_result2[0]['Id'];
        } else {
            $output = array('response' => 'fail','message' => 'Could not detect ID of the new row.');
            return $output;
        }

        $convertCarbon = Carbon::createFromFormat('d/m/Y', $startdate);
        $convertedDate = $convertCarbon->format('Y-m-d ');
        $time = date('Y-m-d h:i:s', strtotime($add_time, strtotime($convertedDate)));


        $result = $this->infusionsoftService->data()->update($fieldto[0], $queryid, array($fieldto[1] => $time));

        $output = array('response' => 'success','message' => 'Date changed.', 'result' => $result);
        return $output;
    }

    public function getAllStage($userId, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $stageResult = $this->infusionsoftService->data()->query('Stage', 1000, 0, ['StageName' => '%'], array('Id','StageName'), "StageOrder", false);
        return $stageResult;
    }

    public function getAllMerchants($userId, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $merchResult = $this->infusionsoftService->data()->getAppSetting('Ecommerce', 'defaultmerchant');
        return $merchResult;
    }

    public function fetAllContacts()
    {
        $contact_result2 = $this->infusionsoftService->data()->query("Contact", 100, 0, ["Id"=>"%%"], ["FirstName", "LastName", "Email"], 'Id', false);
        return $contact_result2;
    }

    public function fetAllContactsWPostData()
    {
        $contact_result2 = $this->infusionsoftService->data()->query("Contact", 1000, 0, ["PostalCode"=>"~<>~","Country" => "~<>~"], ["Id", "PostalCode","Country"], 'Id', false);
        return $contact_result2;
    }

    public function deleteTag($userId, $accountId, $tag_id)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $merchResult = $this->infusionsoftService->data()->delete('ContactGroup', $tag_id);
        return $merchResult;
    }

    public function getContacts($userId, $accountId, $contactId="", $return = [])
    {
        if (count($return) == 0) {
            $return = ["FirstName", "LastName", "Email",'Id'];
        }
        $query = [];
        if ($contactId == "") {
            $query = ["Id"=>"%%"];
        } else {
            $query = ["Id"=>"$contactId"];
        }
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->data()->query("Contact", 1000, 0, $query, $return, 'Id', false);
        return $result;
    }

    /**
     * Get Contact Ids
     *      - based on a filter
     *
     * @param $userId
     *        $accountId
     *        $filter
     *        array $select
     * @return array
     */
    public function getContactIdsByFilter($userId, $accountId, $filter, $select = [])
    {
        if (count($select) == 0) {
            $select = ['Id'];
        }

        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        $result = $this->infusionsoftService->data()->query("Contact", 1000, 0, $filter, $select, 'Id', false);
        return $result;
    }

    public function getLastOrders($userId, $accountId, $contactID, $return = [])
    {
        if (count($return) == 0) {
            $return = ["Id", "JobTitle", "LastUpdated",'OrderType','OrderStatus', "DateCreated", "DueDate"];
        }
        $query = [];
        if ($contactID == "") {
            return [];
        } else {
            $query = ["ContactId"=>"$contactID"];
        }
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->data()->query("Job", 1, 0, $query, $return, 'Id', false);
        if (is_array($result) && count($result) > 0) {
            $result = $result[0];
        } else {
            $result = array();
        }
        return $result;
    }

    public function getAllOrdersUsingCond($userId, $accountId, $query = [], $return = [])
    {
        if (count($return) == 0) {
            $return = ["Id", "JobTitle", "LastUpdated",'OrderType','OrderStatus', "DateCreated", "DueDate"];
        }

        if (count($query) == 0) {
            $query = ["Id"=>"%%"];
        }

        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->data()->query("Job", 1000, 0, $query, $return, 'Id', false);
        if (is_array($result) && count($result) > 0) {
        } else {
            $result = array();
        }
        return $result;
    }

    public function fetchProductUsingIdForUser($userId, $accountId, $product_id)
    {
        try {
            $this->checkIfRefreshTokenHasExpired($userId, $accountId);
            $infs_obj = $this->infusionsoftService->data();

            $product_result = $infs_obj->query("Product", 1, 0, ["Id"=>$product_id], ["Id","ProductName","ProductPrice", "Sku"], "Id", false);
            if (is_array($product_result) && count($product_result) > 0) {
                return $product_result[0];
            }
        } catch (Exception $e) {
        }
        return [];
    }

    public function getOrderItems($userId, $accountId, $orderId)
    {
        $return = ["Id", "ItemName", "ItemDescription",'ItemType','Notes','CPU','PPU','ProductId','Qty','SubscriptionPlanId'];
        $query = ["OrderId"=>$orderId];

        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->data()->query("OrderItem", 1, 0, $query, $return, 'Id', false);
        $return = [];
        foreach ($result as $order_item) {
            $product = $this->fetchProductUsingIdForUser($userId, $accountId, $order_item["ProductId"]);
            $order_item["SKU"] = isset($product["Sku"])?$product["Sku"]:"";
            $return[] = $order_item;
        }
        return $return;
    }


    public function getUsers($userId, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        $result = $this->infusionsoftService->data()->query("User", 1000, 0, ["Id"=>"%%"], ["FirstName", "LastName", "Email",'Id'], 'Id', false);
        return $result;
    }

    public function fetchDataFromINFSRecursive($table, $limit, $query, $rFields, $orderBy, $ascending)
    {
        $data_ar = array();
        $app = $this->infusionsoftService->data();
        try {
            $page = -1;

            while (true) {
                $page++;
                $result = $app->query($table, $limit, $page, $query, $rFields, $orderBy, $ascending);
                foreach ($result as $data) {
                    $data_ar[] = $data;
                }
                if ($limit == 1 || count($result) < $limit) {
                    break;
                }
            }
        } catch (Exception $e) {
            if ($_REQUEST["debug"]) {
                var_dump($e);
            }
        }
        return $data_ar;
    }

    public function fetchContactWithGroupId($userId, $accountId, $tagid)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        #check if the tag exist, else add
        $result = $this->fetchDataFromINFSRecursive("ContactGroupAssign", 1000, ["GroupId"=>$tagid], ["ContactId"], "ContactId", true);

        return $result;
    }

    public function searchContact($userId, $accountId, $query)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);

        #check if the tag exist, else add
        $result = $this->fetchDataFromINFSRecursive("Contact", 1000, $query, ["Id", "PostalCode","Country"], "Id", true);

        return $result;
    }

    public function search($userId, $type, $query, $columns)
    {
        $this->checkIfRefreshTokenHasExpired($userId);

        $result = $this->fetchDataFromINFSRecursive($type, 1000, $query, $columns, "Id", true);

        return $result;
    }

    public function getContactCustomFields($userId, $accountId, $table)
    {
        try {
            $this->checkIfRefreshTokenHasExpired($userId, $accountId);
            switch ($table) {
                case "Contact": $FormId = -1; break;
                case "Opportunity": $FormId = -4; break;
                case "Company": $FormId = -6; break;
                case "Job": $FormId = -9; break;
                default: $FormId = -1;
            }

            $result = $this->infusionsoftService->data()->query("DataFormField", 1000, 0, array('FormId' =>$FormId), array('Name', 'Label'), 'Id', true);
            $return = [];
            foreach ($result as $single) {
                $return[$single["Name"]] = $single;
            }
            return $return;
        } catch (Exception $e) {
            var_dump($e);
            $this->getContactCustomFields($userId, $accountId, $table);
        }
    }

    #function to check if the value is existing in the custom field dropdown, added by Sunil
    public function processCustomFieldWithData($infs_table, $field, $val, $field_type="Text")
    {
        try {
            if (strtolower($field_type) == "date") {
                $field_type = "Date";
            } elseif (strtolower($field_type) == "datetime") {
                $field_type = "DateTime";
            } elseif (strtolower($field_type) == "list") {
                $field_type = "Select";
            } elseif (strtolower($field_type) == "textarea") {
                $field_type = "TextArea";
            } elseif (strtolower($field_type) == "text") {
                $field_type = "Text";
            }

            if (is_string($val) && in_array($field_type, ["Select"]) && trim($val) == "") {
                return;
            }

            if (is_array($val) && count($val) == 0 && in_array($field_type, ["Select"])) {
                return;
            }

            #remove _ from the start of the field name
            $field = ltrim($field, "_");

            #fetch infusionsoft
            $data_service = $this->infusionsoftService->data();

            $table = "DataFormField";

            switch ($infs_table) {
                case "Contact": $FormId = -1; break;
                case "Opportunity": $FormId = -4; break;
                case "Company": $FormId = -6; break;
                case "Job": $FormId = -9; break;
                default: $FormId = -1;
            }
            $query = array("Name"=>"$field", "FormId"=>$FormId);

            $result = $data_service->query("$table", 100, 0, $query, array("Id", "Label", "Name", "DataType", "Values"), "Id", true);

            if (is_array($result) && count($result) > 0 && isset($result[0]["Id"])) {
                $this->updateValueInCustomField($result[0], $val, $field_type);
            } else { # add the custom field
                #finding the headerID for Contact table
                $header_res = $data_service->query("DataFormTab", 1, 0, array("FormId"=>$FormId), array("Id"), "Id", true);

                $headerID = 1;
                if (is_array($header_res) && count($header_res) > 0 && isset($header_res[0]["Id"])) {
                    $header_res1 = $data_service->query("DataFormGroup", 1, 0, array("TabId"=>$header_res[0]["Id"]), array("Id"), "Id", true);

                    if (is_array($header_res1) && count($header_res1) > 0 && isset($header_res1[0]["Id"])) {
                        $headerID = $header_res1[0]["Id"];
                    }
                }

                $customFieldId = $data_service->addCustomField($infs_table, $field, $field_type, $headerID);

                if ($field_type == "Select" && $customFieldId > 0) {
                    $data_service->updateCustomField($customFieldId, $val);
                }
            }
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return $this->processCustomFieldWithData($infs_table, $field, $val, $field_type);
        } catch (\Infusionsoft\Http\HttpException $no_record_found) {
            if (stripos($no_record_found->getMessage(), "An HTTP error occurred: Unauthorized") !== false) {
                return $this->processCustomFieldWithData($infs_table, $field, $val, $field_type);
            }
        }
    }

    public function updateValueInCustomField($data, $val, $type)
    {
        try {
            if (!in_array($type, ["list", "Select"])) {
                return;
            }
            #fetch infusionsoft
            $data_service = $this->infusionsoftService->data();

            $default_values = array();
            if (isset($data["Values"])) {
                $default_values =  $data["Values"];
            }

            $val_to_update = ["Values"=>[]];

            if (is_string($val) && trim($val) != ""  && !in_array($val, $default_values)) {
                $default_values[] = trim($val);
                $val_to_update["Values"][] = $val;
            } elseif (is_array($val)) {
                foreach ($val as $single_val) {
                    if (!in_array($single_val, $default_values) && trim($single_val) != "") {
                        $default_values[] = trim($single_val);
                        $val_to_update["Values"][] = trim($single_val);
                    }
                }
            }
            if (count($val_to_update["Values"]) > 0) {
                $data_service->updateCustomField((int)$data["Id"], $val_to_update);
            }
        } catch (Exception $e) {
        }
        return $default_values;
    }

    #function to fetch all custom fields for the required table
    public function fetchCustomFieldWithData($userId, $accountId, $infs_table)
    {
        $this->checkIfRefreshTokenHasExpired($userId, $accountId);
        try {
            #fetch infusionsoft
            $data_service = $this->infusionsoftService->data();

            $table = "DataFormField";

            switch ($infs_table) {
                case "Contact": $FormId = -1; break;
                case "Opportunity": $FormId = -4; break;
                case "Company": $FormId = -6; break;
                case "Job": $FormId = -9; break;
                default: $FormId = -1;
            }
            $query = array("FormId"=>$FormId);

            $result = $data_service->query($table, 100, 0, $query, array("Id", "Label", "Name", "DataType", "Values"), "Id", true);
            $return = [];

            foreach ($result as $single_res) {
                if (!isset($single_res["Values"])) {
                    $single_res["Values"] = [];
                } else {
                    $single_res["Values"] = explode("\n", $single_res["Values"]);
                }

                $return[$single_res["Name"]] = $single_res;
            }
            return $return;
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return $this->fetchCustomFieldWithData($userId, $accountId, $accountId);
        } catch (\Infusionsoft\Http\HttpException $no_record_found) {
            if (stripos($no_record_found->getMessage(), "An HTTP error occurred: Unauthorized") !== false) {
                return $this->fetchCustomFieldWithData($userId, $accountId, $infs_table);
            }
        }
    }#end of function

    public function createCategory($tag_category="", $accountId)
    {
        $this->checkIfRefreshTokenHasExpired(\Auth::id(), $accountId);

        try {
            $tag_category_id = 0;
            if ($tag_category == "") {
                return $tag_category_id;
            }

            #check if the tag category exist, else add
            $result = $this->infusionsoftService->data()->query("ContactGroupCategory", 1, 0, ["CategoryName"=>$tag_category], ["Id"], "Id", true);

            if (is_array($result) && count($result) > 0) {
                $tag_category_id = $result[0]["Id"];
            } else {
                $tag_category_id = $this->infusionsoftService->data()->add("ContactGroupCategory", [ "CategoryName"=>$tag_category ]);
            }
            return $tag_category_id;
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return $this->manageTagCategory($tag_category);
        } catch (\Infusionsoft\Http\HttpException $no_record_found) {
            if (stripos($no_record_found->getMessage(), "An HTTP error occurred: Unauthorized") !== false) {
                return $this->manageTagCategory($tag_category);
            }
        }
    }

    public function createDocTags($catId, $tagName, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired(\Auth::id(), $accountId);

        try {
            $tag_id = 0;
            if ($tagName == "") {
                return $tag_id;
            }

            $query = ["GroupName"=>$tagName];
            if ($catId > 0) {
                $query["GroupCategoryId"] = $catId;
            }

            #check if the tag category exist, else add
            $result = $this->infusionsoftService->data()->query("ContactGroup", 1, 0, $query, ["Id"], "Id", true);
            if (is_array($result) && count($result) > 0) {
                $tag_id = $result[0]["Id"];
            } else {
                $tag_id = $this->infusionsoftService->data()->add('ContactGroup', [
                    'GroupCategoryId' => $catId,
                    'GroupName' => $tagName,
                    'GroupDescription' => 'this is a test description',
                ]);
            }

            return $tag_id;
        } catch (\Infusionsoft\TokenExpiredException $e) {
            return $this->manageTag($tagName, $catId);
        } catch (\Infusionsoft\Http\HttpException $no_record_found) {
            if (stripos($no_record_found->getMessage(), "An HTTP error occurred: Unauthorized") !== false) {
                return $this->manageTag($tagName, $catId);
            }
        }
    }

    /**
     * Get latest opportunity by ContactId
     */
    public function fetchLastleadDetails($ContactID, $user_id=0, $accountId)
    {
        $this->checkIfRefreshTokenHasExpired($user_id, $accountId);
        try {
            $pricing = array();
            $response = array();

            $infs_obj = $this->infusionsoftService->data();

            $result = $infs_obj->query("Lead", 1, 0, ["ContactID"=>$ContactID], ['Id'], 'LastUpdated', false);

            if (is_array($result) && count($result) > 0) {
                $result = $result[0];
                $bundle = $infs_obj->query("ProductInterest", 100, 0, ["ObjectId"=>$result['Id']], ["DiscountPercent", "ObjType", "ProductId", "ProductType", "Qty", "SubscriptionPlanId"], "Id", false);

                foreach ($bundle as $data) {
                    if (isset($data["ProductId"]) && $data["ProductId"] > 0) {
                        $product_result = $this->fetchProductUsingId($data["ProductId"], $user_id);

                        if (is_array($product_result) && count($product_result) > 0) {
                            $data["ProductName"] = isset($product_result["ProductName"])?$product_result["ProductName"]:"";
                            $data["ProductPrice"] = isset($product_result["ProductPrice"])?$product_result["ProductPrice"]:"";
                            $data["ProductDiscountPrice"] = ($data["DiscountPercent"] != 0)?($data["ProductPrice"] * $data["DiscountPercent"]) / 100:0;

                            $pricing[] = $data;
                        }#end of product result checking if
                    }#end of ProductId set chekcing if
                    if (isset($data["SubscriptionPlanId"]) && $data["SubscriptionPlanId"] > 0) {
                        $subs_plan_result = $infs_obj->query("SubscriptionPlan", 1, 0, ["Id"=>$data["SubscriptionPlanId"]], ["Id","ProductId","PlanPrice", "Cycle"], "Id", false);

                        if (is_array($subs_plan_result) && count($subs_plan_result) > 0) {
                            $subs_plan_result = $subs_plan_result[0];

                            $product_result = $this->fetchProductUsingId($subs_plan_result["ProductId"], $user_id);

                            $data["ProductName"] = isset($product_result["ProductName"])?$product_result["ProductName"]:"";

                            $data["ProductPrice"] = isset($subs_plan_result["PlanPrice"])?$subs_plan_result["PlanPrice"]:"";

                            $data["Cycle"] = isset($subs_plan_result["Cycle"])?$subs_plan_result["Cycle"]:"";

                            $data["ProductDiscountPrice"] = ($data["DiscountPercent"] != 0)?($data["ProductPrice"] * $data["DiscountPercent"]) / 100:0;

                            $pricing[] = $data;
                        }#end of subscription plan result checking if
                    }#end of SubscriptionPlanId set chekcing if
                }#end of loop
                return $pricing;
            }
        } catch (Exception $e) {
        }
        return [];
    }
    //fetch opps stages and filter it for open stages only
    public function fetchOppsStages()
    {
        $opps_stages = [];
        try {
            $opps_stages_all = json_decode($this->infusionsoftService->opportunities()->stage_pipeline()->toJson(), true);
            foreach ($opps_stages_all as $single_stage) {
                if ($single_stage["end_stage"] == 1) {
                    continue;
                }
                $opps_stages[] = $single_stage["stage_id"];
            }
        } catch (Exception $e) {
        }
        return $opps_stages;
    }


    public function create($data, $type, $userId) {
        Log::info('Creating item to infs - type: ' . $type . ' data: '. json_encode($data));

        $this->checkIfRefreshTokenHasExpired($userId);

        // call to infusionsoft
        $result = $this->infusionsoftService->data()->add($type, $data);

        Log::info('INFS item created - Type: ' . $type . ' id: ' . $result);

        return $result;
    }

    public function update($id, $data, $type, $userId) {
        Log::info('Updating item to infs - type: ' . $type . ' data: '. json_encode($data));

        $this->checkIfRefreshTokenHasExpired($userId);

        // call to infusionsoft
        $result = $this->infusionsoftService->data()->update($type, $id, $data);

        Log::info('INFS item updated - Type: ' . $type . ' id: ' . $result);

        return $result;
    }

    public function tag($id, $tags, $type) {
        Log::info('Tagging type - ' . $type . ' id - ' . $id . ' data: ' . json_encode($tags));

        // skip if there are no tags
        if (!isset($tags) || is_null($tags)) {
            return false;
        }

        // get the infs item
        $item = $this->retrieve($id, $type);

        // update the tags
        $item->addTags($tags);
    }

    public function retrieve($id, $type) {
        $result = null;

        switch($type) {
            case CsvStatus::INFS_CONTACT:
                $result = $this->infusionsoftService->contacts()->find($id);
                break;
            case CsvStatus::INFS_COMPANY:
                $result = $this->infusionsoftService->companies()->find($id);
                break;
        }

        return $result;
    }
}