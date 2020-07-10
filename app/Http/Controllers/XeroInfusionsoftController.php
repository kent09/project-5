<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\XeroAccounts;

use App\Services\XeroService;
use App\Services\InfusionSoftService;
use App\InfsAccount;
use App\XeroCronSync;

class XeroInfusionsoftController extends Controller
{
    //
    protected $xero_app_id;

    protected $infs_app;

    protected $xero_service;

    protected $infusionSoftService;

    public function __construct(Request $request, InfusionSoftService $infusionSoftService, XeroService $xero_service)
    {
        if (isset($request->xero_app_id)) {
            $xero_app_id = $request->xero_app_id;
        } else {
            $xero_app_id=0;
        }
        if (isset($request->infs_app)) {
            $infs_app = $request->infs_app;
        } else {
            $infs_app=0;
        }
        $this->xero_app_id = $xero_app_id;
        $this->infs_app = $infs_app;

        $this->infusionSoftService = $infusionSoftService;

        $this->xero_service = $xero_service;
    }#end of constructor

    public function authXero(Request $request)
    {
        // $xeroAcc = XeroAccounts::where('app_id',$this->xero_app_id)->first();
        // if( empty($xeroAcc) ){
        //     return redirect('xero-account');
        // }
        if (!\Request::session()->get('xero_oauth_token')) {
            XeroAccounts::where('app_id', $this->xero_app_id)->update([ 'oauth_token' => '', 'oauth_token_secret' => '']);
        }

        $this->xero_service = new XeroService();

        $this->xero_service->validateXeroAPI($this->xero_app_id);

        return redirect('invoices/xeroaccount');
    }

    public function getAccounts($app_id="c4ca4238a0b923820dcc509a6f75849b")
    {
        $this->xero_service = new XeroService();

        $this->xero_service->getAccounts($app_id);
    }

    public function getXeroAccount(Request $request)
    {
        $user = \Auth::user();
        $accounts = XeroAccounts::where('user_id', $user->id)->get();
        
        return view('xero.list', compact('accounts'));
    }

    public function deleteXeroAccount(Request $request)
    {
        $user = \Auth::user();
        $xero = XeroAccounts::where('user_id', $user->id)
                        ->where('id', $this->xero_app_id)
                        ->first();

        if (!$xero) {
            return array( 'status' => 'failed', 'message' => 'Xero account delete failed.' );
        }

        $xero->delete();

        return array( 'status' => 'success', 'message' => 'Xero account delete success.' );
    }

    public function addXeroAccount(Request $request)
    {
        $params = $request->all();
        $user = \Auth::user();

        $limit_reached = XeroAccounts::where('user_id', $user->id)->count();
        if ($limit_reached > 0) {
            return redirect()->back()->with('error', 'You have reached the limit on adding Xero Account.');
        }

        $getxero = XeroAccounts::where('app_name', $params['name'])->where('user_id', $user->id)->get();
        if (count($getxero) > 0) {
            return redirect()->back()->with('error', 'Xero account already created with this name.');
        }

        $xeroAccId = XeroAccounts::create([ 'user_id' => $user->id ,'app_name' => $params['name'] ]);

        $appId = md5($xeroAccId->id);
        XeroAccounts::where('id', $xeroAccId->id)->update([ 'app_id' => $appId ]);

        $xero = new XeroService();
        $response = $xero->validateXeroAPI($appId);
        var_dump($response);
        exit;
        if (isset($response['url']) && !empty($response['url'])) {
            return array( 'status' => 'success' , 'url' => $response['url'] );
        }

        return redirect()->back()->with('error', 'Xero account is failed to add.');
    }

    public function processOrderIntoINFS(Request $request)
    {
        $params = $request->all();
        $mode = isset($params["mode"])?$params["mode"]:"";
        $FuseKey = isset($params["FuseKey"])?$params["FuseKey"]:"";
        $app = isset($params["app"])?$params["app"]:"";
        $contactID = isset($params["contactID"])?$params["contactID"]:"";
        $SalesAccount = isset($params["SalesAccount"])?$params["SalesAccount"]:"";
        $InvoiceStatus = isset($params["InvoiceStatus"])?$params["InvoiceStatus"]:"";
        $TaxStatus = isset($params["TaxStatus"])?$params["TaxStatus"]:"";
        $xero_app_id = isset($params["xero_app_id"])?$params["xero_app_id"]:"";

        if ($mode != "xero_invoice_copy" || $FuseKey == "" || $app == "" || $contactID == "" || $xero_app_id == "") {
            die("Required data missing.");
        }

        #fetch User details
        $user_res = \App\User::where("FuseKey", $FuseKey)->first();
        if (!isset($user_res->id)) {
            return "User not found.";
        }

        $userId = $user_res->id;

        #verify INFS application
        $infs_res = InfsAccount::where("user_id", $userId)->where("name", $app)->first();
        if (!isset($infs_res->id)) {
            return "INFS account not found.";
        }

        #verify Xero application
        $xero_res = \App\XeroAccounts::where("app_id", $xero_app_id)->where("user_id", $userId)->first();
        if (!isset($xero_res->id)) {
            return "Xero account not found.";
        }

        #find all xero settings which we have for this account
        $xero_settings_res = \App\xeroInvoiceSetting::where("xero_id", $xero_res->id)->where("user_id", $userId)->first();
        if (empty($xero_settings_res) || count($xero_settings_res) == 0) {
            return "Xero account settings not found.";
        }

        $accountId = $infs_res->id;

        #make sure we have XeroID and XeroInvoiceID field created in the INFS account provided
        $this->infusionSoftService->checkIfRefreshTokenHasExpired($userId, $accountId);
        $contact_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Contact");
        #if field is not existing the create field in contact table
        if (!array_key_exists("XeroID", $contact_fields)) {
            $this->infusionSoftService->processCustomFieldWithData("Contact", "_XeroID", "", "text");
            $contact_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Contact");
            if (!array_key_exists("XeroID", $contact_fields)) {
                return "XeroID creation failed in Contact table. Please create it manually, otherwise sync will not work.";
            }
        }
        #fetch field from tag in Job table
        $job_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Job");
        #if field is not existing the create field in Job table
        if (!array_key_exists("XeroInvoiceID", $job_fields)) {
            $this->infusionSoftService->processCustomFieldWithData("Job", "_XeroInvoiceID", "", "text");
            $job_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Job");
            if (!array_key_exists("XeroInvoiceID", $job_fields)) {
                return "XeroInvoiceID creation failed in Job table. Please create it manually, otherwise sync will not work.";
            }
        }

        #fetch contact details using contact ID
        $xero_dedupe_settings = json_decode($xero_settings_res->settings, true);
        $return = ["Id", "FirstName", "LastName", "Email", "Company", "Phone1", "StreetAddress1", "StreetAddress2", "City", "State", "PostalCode", "Country", "_XeroID"];
        if (isset($xero_dedupe_settings["company"]) && $xero_dedupe_settings["company"] != "" && !in_array($xero_dedupe_settings["company"], $return)) {
            $return[] = $xero_dedupe_settings["company"];
        }
        foreach ($xero_dedupe_settings["infs_fields"] as $dedupe_field) {
            if ($dedupe_field != "" && !in_array($dedupe_field, $return)) {
                $return[] = $dedupe_field;
            }
        }
        $infs_contact = $this->infusionSoftService->getContacts($userId, $accountId, $contactID, $return);

        #find orders from INFS for the contact id
        if (is_array($infs_contact) && count($infs_contact) > 0) {
            $infs_contact = $infs_contact[0];
            #get last order
            $infs_return = ["Id", "JobTitle", "LastUpdated",'OrderType','OrderStatus', "DateCreated", "DueDate", "_XeroInvoiceID"];
            $infs_last_order = $this->infusionSoftService->getLastOrders($userId, $accountId, $contactID, $infs_return);
            if (array_key_exists("Id", $infs_last_order)) {
                $order_items = $this->infusionSoftService->getOrderItems($userId, $accountId, $infs_last_order["Id"]);
                #if we have order items then start searching if, we have contact in xero
                if (is_array($order_items) && count($order_items) > 0) {
                    $infs_last_order["OrderItems"] = $order_items;
                    #search contact using xero id, if we have it available
                    $xero_contact = $this->xero_service->searchContact($xero_res, $infs_contact, $xero_settings_res);
                    $infs_contact["_XeroID"] = $xero_contact["XeroID"];
                    $infs_contact["contact"] = $xero_contact["contact"];
                }
            }
        } else {
            return "No contact found matching the contact ID";
        }

        if (is_array($xero_contact) && in_array($xero_contact["status"], ["new", "existing"])) {
            #update XERO_ID
            if (isset($xero_contact["update_infs"]) && $xero_contact["update_infs"] == true) {
                $this->infusionSoftService->updateDataInInfusionSoftClient($userId, $accountId, "Contact", $infs_contact["Id"], ["_XeroID"=>$xero_contact["XeroID"]]);
            }

            #process order here
            $xero_orders = $this->xero_service->searchOrder($xero_res, $infs_contact, $infs_last_order, $xero_dedupe_settings, $SalesAccount, $InvoiceStatus, $TaxStatus);
            #check if orders were processed in the xero
            if (isset($xero_orders["update_infs"]) && $xero_orders["update_infs"] == true && isset($xero_orders["XeroInvoiceID"]) && $xero_orders["XeroInvoiceID"] != "") {
                #update the xero invoice id in the infusionsoft orders for future reference
                $this->infusionSoftService->updateDataInInfusionSoftClient($userId, $accountId, "Job", $xero_orders["OrderId"], ["_XeroInvoiceID"=>$xero_orders["XeroInvoiceID"]]);
            }
        }

        echo "<pre>";
        print_r($xero_contact);
        print_r($infs_contact);
        print_r($infs_last_order);
        print_r($order_items);
        print_r($xero_orders);
        exit;
    }

    public function processOrderIntoINFSCron()
    {
        $now = date("Y-m-d H:i:s");
        $now_24 = date("Y-m-d H:i:s", strtotime("-48 hours", strtotime($now)));
        
        #fetch all Xero Cron Sync rows which has status=1
        $XeroCronSync_Res = XeroCronSync::where("status", 1)->get();
        foreach ($XeroCronSync_Res as $cron_sync) {
            $userId = $cron_sync->user_id;
            $xero_id = $cron_sync->xero_id;
            $accountId = $cron_sync->infusionsoft_account_id;

            #verify Xero application
            $xero_res = \App\XeroAccounts::where("id", $xero_id)->first();
            if (!isset($xero_res->id)) {
                continue;
            }

            #validating the infusionsoft account
            $this->infusionSoftService->checkIfRefreshTokenHasExpired($userId, $accountId);

            $contact_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Contact");
            #if field is not existing the create field in contact table
            if (!array_key_exists("XeroID", $contact_fields)) {
                $this->infusionSoftService->processCustomFieldWithData("Contact", "_XeroID", "", "text");
                $contact_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Contact");
                if (!array_key_exists("XeroID", $contact_fields)) {
                    return "XeroID creation failed in Contact table. Please create it manually, otherwise sync will not work.";
                }
            }
            #fetch field from tag in Job table
            $job_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Job");
            #if field is not existing the create field in Job table
            if (!array_key_exists("XeroInvoiceID", $job_fields)) {
                $this->infusionSoftService->processCustomFieldWithData("Job", "_XeroInvoiceID", "", "text");
                $job_fields = $this->infusionSoftService->getContactCustomFields($userId, $accountId, "Job");
                if (!array_key_exists("XeroInvoiceID", $job_fields)) {
                    return "XeroInvoiceID creation failed in Job table. Please create it manually, otherwise sync will not work.";
                }
            }

            #fetch contact details using contact ID
            $xero_dedupe_settings = json_decode($cron_sync->settings, true);
            $return = ["Id", "FirstName", "LastName", "Email", "Company", "Phone1", "StreetAddress1", "StreetAddress2", "City", "State", "PostalCode", "Country", "_XeroID"];
            if (isset($xero_dedupe_settings["company"]) && $xero_dedupe_settings["company"] != "" && !in_array($xero_dedupe_settings["company"], $return)) {
                $return[] = $xero_dedupe_settings["company"];
            }
            foreach ($xero_dedupe_settings["infs_fields"] as $dedupe_field) {
                if ($dedupe_field != "" && !in_array($dedupe_field, $return)) {
                    $return[] = $dedupe_field;
                }
            }
            #this array will store the contact details and have ContactId as the key, this will prevent the fetching of same contact details on case of multiple order
            $order_contacts = [];
            
            #fetch last 24 hours order for syncing
            $query = ["LastUpdated"=>"~>=~$now_24 ~<=~$now"];
            $infs_return = ["Id", "JobTitle", "LastUpdated",'OrderType','OrderStatus', "DateCreated", "DueDate", "_XeroInvoiceID", "ContactId"];
            $order_last_24 = $this->infusionSoftService->getAllOrdersUsingCond($userId, $accountId, $query, $infs_return);
            
            foreach ($order_last_24 as $single_order) {
                #fetch contact details if not already in the order_contacts array
                if (!isset($order_contacts[$single_order["ContactId"]])) {
                    $infs_contact = $this->infusionSoftService->getContacts($userId, $accountId, $single_order["ContactId"], $return);
                    $order_contacts[$single_order["ContactId"]] = $infs_contact[0];
                }
                $infs_contact = $order_contacts[$single_order["ContactId"]];

                #make sure the infs contact is having valid information
                if (isset($infs_contact["Id"])) {
                    $infs_last_order = $single_order;
                    if (array_key_exists("Id", $infs_last_order)) {
                        $order_items = $this->infusionSoftService->getOrderItems($userId, $accountId, $infs_last_order["Id"]);
                        #if we have order items then start searching if, we have contact in xero
                        if (is_array($order_items) && count($order_items) > 0) {
                            $infs_last_order["OrderItems"] = $order_items;
                            #search contact using xero id, if we have it available
                            $xero_contact = $this->xero_service->searchContact($xero_res, $infs_contact, $cron_sync);
                            $infs_contact["_XeroID"] = $xero_contact["XeroID"];
                            $infs_contact["contact"] = $xero_contact["contact"];
                        }
                    }
                } else {
                    continue;
                }

                if (is_array($xero_contact) && in_array($xero_contact["status"], ["new", "existing"])) {
                    #update XERO_ID
                    if (isset($xero_contact["update_infs"]) && $xero_contact["update_infs"] == true) {
                        $this->infusionSoftService->updateDataInInfusionSoftClient($userId, $accountId, "Contact", $infs_contact["Id"], ["_XeroID"=>$xero_contact["XeroID"]]);
                    }

                    #process order here
                    $xero_orders = $this->xero_service->searchOrder($xero_res, $infs_contact, $infs_last_order, $xero_dedupe_settings, "", "", "");
                    #check if orders were processed in the xero
                    if (isset($xero_orders["update_infs"]) && $xero_orders["update_infs"] == true && isset($xero_orders["XeroInvoiceID"]) && $xero_orders["XeroInvoiceID"] != "") {
                        #update the xero invoice id in the infusionsoft orders for future reference
                        $this->infusionSoftService->updateDataInInfusionSoftClient($userId, $accountId, "Job", $xero_orders["OrderId"], ["_XeroInvoiceID"=>$xero_orders["XeroInvoiceID"]]);
                    }
                }
            }#end of loop
            
            echo "<pre>";
            print_r($order_last_24);
            echo "</pre>";
        }#end of cron sync loop
        exit;
    }#end of function


    public function returnXeroInvoiceCopy() {
        return view('scripts.xeroInvoiceCopy');
    }

    public function returnXeroInvoiceCron() {
        return view('scripts.xeroInvoiceCron');
    }

}#end of class
