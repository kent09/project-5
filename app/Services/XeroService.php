<?php
/**
 * Created by PhpStorm.
 * User: Sunil
 * Date: 10/25/17
 * Time: 10:09 AM
 */

namespace App\Services;

use App\Repoistry\XeroRepoistry;
use App\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

use XeroPHP\Application\PublicApplication;
use XeroPHP\Remote\Request as XeroRequest;
use XeroPHP\Remote\URL;

class XeroService
{
    protected $XRO_APP_TYPE = "Public";
    
    protected $OAUTH_CALLBACK = "https://app.fusedtools.com/invoices/xeroauth";
    
    public $XeroOAuth;

    public $consumer_key;
    public $consumer_secret;
    public $useragent;

    public function __construct()
    {
        $this->consumer_key = env('XERO_CLIENT_ID');
        $this->consumer_secret = env('XERO_CLIENT_SECRET');
        $this->useragent = env('XERO_AGENT');
    }

    #function for validating the xero API
    public function validateXeroAPI($app_id)
    {
        try {
            @session_start();
            $config = [
                'oauth' => [
                    'callback'   => $this->OAUTH_CALLBACK,
                    'consumer_key'   => $this->consumer_key,
                    'consumer_secret'   => $this->consumer_secret
                ],
                'curl'  => [
                    CURLOPT_USERAGENT      => $this->useragent
                ]
            ];

            if (isset($_REQUEST["oauth_token"]) && ($app_id == "" || $app_id == 0)) {
                $app_id_res = \App\XeroAccounts::where("oauth_token", $_REQUEST["oauth_token"])->first();

                if (!isset($app_id_res->app_id)) {
                    die("Something went wrong, pease try adding the account agian.");
                }
                $app_id = $app_id_res->app_id;
            }

            $xero = new PublicApplication($config);
            $oauth_session = $this->getOAuthSession($app_id);

            //if no session or if it is expired
            if (null === $oauth_session) {
                $url = new URL($xero, URL::OAUTH_REQUEST_TOKEN);
                $request = new XeroRequest($xero, $url);
                //Here's where you'll see if your keys are valid.
                //You can catch a BadRequestException.
                try {
                    $request->send();
                } catch (Exception $e) {
                    \Log::info('Xero validation exception'. print_r($e->getMessage(), true));
                    if ($request->getResponse()) {
                        \Log::info('Xero validation response'. print_r($request->getResponse()->getOAuthResponse(), true));
//                        print_r($request->getResponse()->getOAuthResponse());
                    }
                }
                $oauth_response = $request->getResponse()->getOAuthResponse();
                
                \Request::session()->put('xero_oauth_token', $oauth_response['oauth_token']);
                
                $this->setOAuthSession(
                    $app_id,
                    $oauth_response['oauth_token'],
                    $oauth_response['oauth_token_secret']
                );
                header(
                    sprintf(
                        'Location: %s',
                        $xero->getAuthorizeURL($oauth_response['oauth_token'])
                    )
                );
                
                // $client = new Client();
                // $res = $client->get($xero->getAuthorizeURL($oauth_response['oauth_token']));
                
               
                exit;
            } else {
                \Request::session()->forget('xero_oauth_token');
                $xero->getOAuthClient()
                    ->setToken($oauth_session['oauth_token'])
                    ->setTokenSecret($oauth_session['oauth_token_secret']);
                if (isset($_REQUEST['oauth_verifier'])) {
                    $xero->getOAuthClient()->setVerifier($_REQUEST['oauth_verifier']);
                    $url = new URL($xero, URL::OAUTH_ACCESS_TOKEN);
                    $request = new XeroRequest($xero, $url);
                    $request->send();
                    $oauth_response = $request->getResponse()->getOAuthResponse();

                    $this->setOAuthSession(
                        $app_id,
                        $oauth_response['oauth_token'],
                        $oauth_response['oauth_token_secret'],
                        $oauth_response['oauth_expires_in']
                    );
                    //drop the qs
                    $uri_parts = explode('?', $_SERVER['REQUEST_URI']);
                }
            }
            $this->XeroOAuth = $xero;
            //Otherwise, you're in.
//            echo "<pre>"; print_r($xero->load('Accounting\\Organisation')->execute());
            return array('status' =>'success');
        } catch (UnauthorizedException $e) {
            \App\XeroAccounts::where("app_id", $app_id)->update(["oauth_token"=>"", "oauth_token_secret"=>"", "oauth_expires_in"=>""]);
            $this->validateXeroAPI($app_id);
        } catch (Exception $e) {
//            var_dump($e);
            return array('status' =>'failed');
        }
    }

    public function setOAuthSession($app_id, $token, $secret, $expires = null)
    {
        // expires sends back an int
        if ($expires !== null) {
            $expires = time() + intval($expires);
        }
        \App\XeroAccounts::where("app_id", $app_id)->update(["oauth_token"=>$token, "oauth_token_secret"=>$secret, "oauth_expires_in"=>$expires]);
    }

    public function getOAuthSession($app_id)
    {
        $xero_account = \App\XeroAccounts::where("app_id", $app_id)->first();
        if (!isset($xero_account->oauth_token) || trim($xero_account->oauth_token) == "") {
            return null;
        }

        return ["oauth_token"=>$xero_account->oauth_token, "oauth_token_secret"=>$xero_account->oauth_token_secret, "oauth_expires_in"=>$xero_account->oauth_expires_in];
    }

    public function getInvoiceStatus()
    {
        $invoice_status = ["DRAFT", "SUBMITTED", "AUTHORISED"];
        return $invoice_status;
    }

    public function getAccounts($app_id)
    {
        $this->validateXeroAPI($app_id);
        $accounts = $this->XeroOAuth->load('Accounting\\Account')->where('Status', \XeroPHP\Models\Accounting\Account::ACCOUNT_STATUS_ACTIVE)->execute();
        $return = [];
        foreach ($accounts as $account) {#$account->AccountID
            $return[] = ["account_id"=>$account->Code, "account_name"=>$account->Name, "Type"=>$account->Type];
            // echo "<pre>"; print_r($account); echo "</pre>";
        }
        return $return;
    }

    public function searchContact($xero_res, $infs_contact, $xero_settings_res)
    {
        if (!is_object($xero_res)) {
            return;
        }
        $this->validateXeroAPI($xero_res->app_id);

        $return_contact = ["status"=>"fail", "XeroID"=>null];

        echo "<pre>";
        print_r($infs_contact);

        $xero = $this->XeroOAuth;

        $contact = [];
        $xero_contact_id = (isset($infs_contact["_XeroID"]))?$infs_contact["_XeroID"]:"";
        $email = (isset($infs_contact["Email"]))?$infs_contact["Email"]:"";
        $FirstName = (isset($infs_contact["FirstName"]))?$infs_contact["FirstName"]:"";
        $LastName = (isset($infs_contact["LastName"]))?$infs_contact["LastName"]:"";
        $Company = (isset($infs_contact["Company"]))?$infs_contact["Company"]:"";
        $address1 = (isset($infs_contact["StreetAddress1"]))?$infs_contact["StreetAddress1"]:"";
        $address2 = (isset($infs_contact["StreetAddress2"]))?$infs_contact["StreetAddress2"]:"";
        $city = (isset($infs_contact["City"]))?$infs_contact["City"]:"";
        $region = (isset($infs_contact["State"]))?$infs_contact["State"]:"";
        $country = (isset($infs_contact["Country"]))?$infs_contact["Country"]:"";
        $postalcode = (isset($infs_contact["PostalCode"]))?$infs_contact["PostalCode"]:"";
        $phone1 = (isset($infs_contact["Phone1"]))?$infs_contact["Phone1"]:"";

        #add contact in xero flag
        $xero_add_account_flag = false;

        $contact_id_from_xero = null;

        if ($xero_contact_id != "") {
            echo "<br>Searching using xero contact id";
            $contact = $this->XeroOAuth->loadByGUID('Accounting\\Contact', $xero_contact_id);
            if (is_object($contact) && count($contact) > 0) {
                $contact_id_from_xero = $contact->getContactID();
                $return_contact["update_infs"] = false;
            }
        }

        if ($contact_id_from_xero == null && $email != "") {
            $xero_dedupe_settings = json_decode($xero_settings_res->settings, true);

            foreach ($xero_dedupe_settings["infs_fields"] as $dedupe_field) {
                switch (strtolower($dedupe_field)) {
                    case "email":
                        echo "<br>Searching using email";
                        $contact = $this->XeroOAuth->load('Accounting\\Contact')->where("EmailAddress", "$email")->execute();
                        if (is_object($contact) && count($contact) > 0) {
                            foreach ($contact as $single_contact) {
                                $contact_id_from_xero = $single_contact->getContactID();
                                $return_contact["update_infs"] = true;
                                break;
                            }
                        }
                        break;
                }
            }
        }
        #if no contact id is set then make the acc account flag to true
        if ($contact_id_from_xero == null) {
            $xero_add_account_flag = true;
        } else {
            $xero_add_account_flag = false;
        }

        if ($xero_add_account_flag == true) {
            #contact address
            $xero_address = new \XeroPHP\Models\Accounting\Address($xero);
            $xero_address->setAddressType("POBOX")
                ->setAddressLine1("$address1")
                ->setAddressLine2("$address2")
                ->setCity("$city")
                ->setRegion("$region")
                ->setPostalCode("$postalcode")
                ->setCountry("$country");
            #contact Phone
            $xero_phone = new \XeroPHP\Models\Accounting\Phone($xero);
            $xero_phone->setPhoneType("DEFAULT")
                ->setPhoneNumber("$phone1");

            #add the contact in xero
            $contact_add = new \XeroPHP\Models\Accounting\Contact($xero);
            
            $Name = "$FirstName $LastName";
            if (isset($xero_dedupe_settings["company"]) && $xero_dedupe_settings["company"] != "" && isset($infs_contact[$xero_dedupe_settings["company"]])) {
                $Name = $infs_contact[$xero_dedupe_settings["company"]];
            }
            
            $contact_add->setName("$FirstName $LastName")
                ->setFirstName("$FirstName")
                ->setLastName("$LastName")
                ->addAddress($xero_address)
                ->setContactNumber("INFS".$infs_contact["Id"])
                ->setEmailAddress("$email");
            $add_status = $contact_add->save();

            #update the xero id in INFS
            $return_contact["status"] = "new";
            $return_contact["XeroID"] = $contact_add->getContactID();
            $return_contact["update_infs"] = true;
            $return_contact["contact"] = $contact_add;
            return $return_contact;
        } else {
            $return_contact["status"] = "existing";
            $return_contact["XeroID"] = $contact_id_from_xero;
            $return_contact["contact"] = $contact;
        }
        return $return_contact;
    }

    public function searchOrder($xero_res, $infs_contact, $infs_orders, $xero_settings_res, $SalesAccount, $InvoiceStatus, $TaxStatus)
    {
        if (!is_object($xero_res)) {
            return;
        }
        $this->validateXeroAPI($xero_res->app_id);

        $xero = $this->XeroOAuth;

        $invoice_id_from_xero = "";

        $return_invoice = ["status"=>"fail", "XeroInvoiceID"=>null];

        if (array_key_exists("_XeroInvoiceID", $infs_orders) && $infs_orders["_XeroInvoiceID"] != "") {
            echo "<br>Searching using xero invoice id";
            $invoice = $this->XeroOAuth->loadByGUID('Accounting\\Invoice', $infs_orders["_XeroInvoiceID"]);
            if (is_object($invoice) && count($invoice) > 0) {
                $invoice_id_from_xero = $invoice->getInvoiceID();
                $return_invoice["update_infs"] = false;
            }
        }


        if ($invoice_id_from_xero == "") {
            echo "<br>Searching using xero invoice number";
            $invoice = $this->XeroOAuth->load('Accounting\\Invoice')->where("Reference", "INFS".$infs_orders["Id"])->execute();
            if (is_object($invoice) && count($invoice) > 0) {
                foreach ($invoice as $single_invoice) {
                    $invoice_id_from_xero = $single_invoice->getInvoiceID();
                    $return_invoice["update_infs"] = true;
                    break;
                }
            }
        }


        #if no invoice id is set then make the add invoice flag to true
        if ($invoice_id_from_xero == "") {
            $xero_add_account_flag = true;
        } else {
            $xero_add_account_flag = false;
        }

        if ($xero_add_account_flag == true) {

            #add the invoice in xero
            $invoice_add = new \XeroPHP\Models\Accounting\Invoice($xero);
            
            #invoice status
            $InvoiceStatus = ($InvoiceStatus != "")?$InvoiceStatus:$xero_settings_res["invoice_status"];

            #Sales account
            $SalesAccount = ($SalesAccount != "")?$SalesAccount:$xero_settings_res["sale_account"];

            #Tax Status
            switch ($TaxStatus) {
                case "2":
                    $TaxStatus = \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_NOTAX;
                    break;
                case "1":
                    $TaxStatus = \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_EXCLUSIVE;
                    break;
                case "0":
                    $TaxStatus = \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_INCLUSIVE;
                    break;
                default:
                    $TaxStatus = \XeroPHP\Models\Accounting\Invoice::LINEAMOUNT_TYPE_INCLUSIVE;
                    break;
            }
            $TaxStatus = ($TaxStatus != "")?$TaxStatus:$xero_settings_res["sale_account"];

            $invoice_add->setType(\XeroPHP\Models\Accounting\Invoice::INVOICE_TYPE_ACCREC)
                ->setReference("INFS".$infs_orders["Id"])
                ->setContact($infs_contact["contact"])
                ->setStatus($InvoiceStatus)
                ->setLineAmountType($TaxStatus);
            if (isset($infs_orders["DateCreated"])) {
                $invoice_add->setDate($infs_orders["DateCreated"]);
            }
            if (isset($infs_orders["DueDate"])) {
                $invoice_add->setDueDate($infs_orders["DueDate"]);
            }

            #Line Items
            foreach ($infs_orders["OrderItems"] as $line_item) {
                $xero_line_item = new \XeroPHP\Models\Accounting\Invoice\LineItem($xero);

                $xero_line_item->setQuantity($line_item["Qty"])
                ->setDescription($line_item["SKU"]." - ".$line_item["ItemName"])
                ->setUnitAmount($line_item["PPU"])
                ->setAccountCode($SalesAccount);
                
                $invoice_add->addLineItem($xero_line_item);
            }#end of loop

            $add_status = $invoice_add->save();

            #update the xero id in INFS
            $return_invoice["status"] = "new";
            $return_invoice["OrderId"] = $infs_orders["Id"];
            $return_invoice["XeroInvoiceID"] = $invoice_add->getInvoiceID();
            $return_invoice["update_infs"] = true;
            return $return_invoice;
        } else {
            $return_invoice["status"] = "existing";
            $return_invoice["XeroInvoiceID"] = $invoice_id_from_xero;
        }
        return $return_invoice;
    }
}
