<?php

namespace App\Http\Controllers;

use App\Services\InfusionSoftService;
use Illuminate\Http\Request;

use App\Http\Requests;
use Auth;
use App\DocsHttpErrors;
use App\User;
use App\InfsAccount;
use App\DocsCompleted;
use App\UserUsageDocs;
use App\DocsAccountsPanda;
use App\PandaRoleSettings;
use App\DocumentCreateLog;

class DocsPandaController extends Controller
{
    private $access_token ="";
    private $fuseddoc_userID ="";
    private $ft_user_email ="";
    private $ft_user_name ="";
    private $contact_id ="";
    private $user ="";
    private $temp_id ="";
    private $roles ="";
    private $app ="";
    private $mails =array();
    
    protected $infusionSoftService;

    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->infusionSoftService = $infusionSoftService;
    }

    /**
    * Receives the INFS http post and process it immediately, Fire a background process to actually process it.
    *
    * @return \Illuminate\Http\Response
    */
    public function createDocument(Request $request)
    {
        $data = $request->all();
        $documentCreateLogRecord = DocumentCreateLog::create(['data' => json_encode($data), 'app' => 'pandadoc']);

        $processUri = url('/panda/process/' . $documentCreateLogRecord->id);
        exec('wget "'.$processUri.'" --delete-after >/dev/null 2>&1 &');
    }

    /**
    * The background process to create document via pandadoc API
    *
    * @return \Illuminate\Http\Response
    */
    public function processCreateDocument($id = false)
    {
        $documentCreateLogRecord = DocumentCreateLog::find($id);
        if (!$documentCreateLogRecord) {
            $this->sendNotification("no_log_record", "No such record " . $id, 'Failed');
            die('No such record.');
        }
        $request = json_decode($documentCreateLogRecord->data);

        $FuseUser = isset($request->FuseUser) ? $request->FuseUser : "";
        $this->fuseddoc_userID = isset($request->FuseKey) ? $request->FuseKey : "";
        $this->app = isset($request->app) ? $request->app : "";
        $this->contact_id = isset($request->contactId) ? $request->contactId : "";
        $templateID = isset($request->TemplateID) ? $request->TemplateID : "";
        $this->temp_id = $templateID;
        $status = (isset($request->Status) && ($request->Status == 1 ||$request->Status == 0)) ? $request->Status: 1;
        $pricingTable = (isset($request->PricingTable)&&($request->PricingTable == 1 ||$request->PricingTable == 0)) ? $request->PricingTable: 0;
       
        if (empty($this->fuseddoc_userID)) {
            $this->sendNotification("missing_mandatory_field", "Missing Mandatory Field FuseUser", 'Failed', [], $documentCreateLogRecord);
            //	$message = $this->getMessage("Missing Mandatory Field FuseUser.");
            //	$this->sendEmail($this->ft_user_email,"FusedTools-PandaDoc Notifications", $message);
            die('Missing Mandatory Field FuseUser.');
        }
        
        if (empty($this->app)) {
            $this->sendNotification("missing_mandatory_field", "Missing Mandatory Field app", 'Failed', [], $documentCreateLogRecord);
            //$message = $this->getMessage("Missing Mandatory Field app.");
            //$this->sendEmail($this->ft_user_email,"FusedTools-PandaDoc Notifications", $message);
            die('Missing Mandatory Field App.');
        }
        
        $this->user = $user = User::where('id', $FuseUser)->first();
        
        if (count($user) == 0 || empty($user)) {
            $getUser = User::where('fusekey', $this->fuseddoc_userID)->first();
            if (!empty($getUser)) {
                $this->mails = \CommanHelper::notifyEmails($getUser->id);
                $this->fuseddoc_userID =  $getUser->id;
                $this->user = $getUser;
                $user = $getUser;
            }
        } else {
            $this->mails = \CommanHelper::notifyEmails($user->id);
           
            $this->fuseddoc_userID = $user->id;
            $message = $this->fuseUserMessage();
            $this->sendEmail($this->mails, "FusedTools-PandaDoc Notifications", $message);
        }
        
        $this->ft_user_email		= $this->user->email;
        
        //Get infs account
        $infsAcc = InfsAccount::where('user_id', $this->user->id)->where('account', $this->app.'.infusionsoft.com')->first();
        if (empty($infsAcc)) {
            $this->sendNotification("missing_mandatory_field", "Infs account not found", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Infs account not found.");
            $this->sendEmail($this->mails, "FusedTools-PandaDoc Notifications", $message);
            die('Infs account not found.');
        } else {
            $this->app = $infsAcc;
        }
        
        $pandaAccount = DocsAccountsPanda::where('user_id', $this->fuseddoc_userID)->first();
        
        if (empty($pandaAccount)) {
            $this->sendNotification("missing_mandatory_field", "Panda Account Not Found.", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Panda Account Not Found.");
            $this->sendEmail($this->mails, "FusedTools-PandaDoc Notifications", $message);
            die('Missing Mandatory Field FuseKey.');
        }
        
        $api_key = $pandaAccount->api_key;
        $this->access_token = $this->getrefereshedAcessToken($api_key, $this->fuseddoc_userID);
        $template_details = $this->getTemplateDetail($templateID);
        
        $document_name = (isset($request->doc_name) && $request->doc_name != '')?$request->doc_name:$template_details['name'];
        
        $docMessage = (isset($request->doc_message) && $request->doc_message != '')?$request->doc_message:'';
        
        $recipients =  array();
        
        foreach ($template_details['roles'] as $role) {
            $r_email = $r_fname = $r_lname = $r_phone = $r_comp = "";
            $rkey = $role->name;
            
            foreach ($request as $key => $value) {
                $input_key = str_replace("_", ".", $key);

                if ($input_key == $rkey.'.Email') {
                    $r_email = $value;
                }
                
                if ($input_key == $rkey.'.FirstName') {
                    $r_fname = $value;
                }
                
                if ($input_key == $rkey.'.LastName') {
                    $r_lname = $value;
                }
                if ($input_key == $rkey.'.Phone') {
                    $r_phone = $value;
                }
                if ($input_key == $rkey.'.Company') {
                    $r_comp = $value;
                }
            }
            
            if ($r_email != "" && $r_fname != "" && $r_lname != "") {
                $recipients[] = array(
                    'email' 		=> $r_email,
                    'first_name' 	=> $r_fname,
                    'last_name' 	=> $r_lname,
                    'role'			=> $role->name,
                    'phone' => $r_phone,
                    'company' => $r_comp
                );
            } else {
                $this->sendNotification("missing_mandatory_field", "You must include $rkey.FirstName, $rkey.LastName and $rkey.Email with your data.", 'Failed', [], $documentCreateLogRecord);
                $message 				= $this->getMessage("You must include $rkey.FirstName, $rkey.LastName and $rkey.Email with your data.<br> Your document has not been generated.");
                $this->sendEmail($this->mails, "FusedDocs-PandaDoc Notifications", $message);
                die('Missing Mandatory Field TemplateID.');
            }
        }
        $this->roles = $recipients;
        
        if (empty($templateID)) {
            $this->sendNotification("missing_mandatory_field", "Missing Required Field: TemplateID", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Required Field: TemplateID.");
            $this->sendEmail($this->mails, "FusedDocs Error: Missing TemplateID", $message);
            die('Missing Mandatory Field: TemplateID.');
        }

        if (empty($this->contact_id)) {
            $this->sendNotification("missing_mandatory_field", "Missing Required Field: ContactID", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Required Field: ContactID.");
            $this->sendEmail($this->mails, "FusedDocs Error: Missing ContactID", $message);
            die('Missing Mandatory Field: ContactID.');
        }
        
        $tokens	 = array();
        $fields	 = array();
        
        foreach ($request as $key => $value) {
            $input_key = str_replace("_", ".", $key);

            if (in_array($input_key, $template_details['tokens'])) {
                $data		   = array();
                $data['name']  = $input_key;
                $data['value'] = $value;
                array_push($tokens, $data);
            }
        }
    
        foreach ($request as $key => $value) {
            $customKey = str_replace('_', ' ', $key);
            if (in_array($key, $template_details['fields'])) {
                $fields[$key]  =array();

                if (stristr($key, 'date')) {
                    $date = date('Y-m-d H:i:s', strtotime($value));
                    $value = str_replace('+00:00', '.000Z', gmdate('c', strtotime($date)));
                }
                
                $fields[$key]  	= array(
                    'value' => $value
                );
            } elseif (in_array(trim($customKey), $template_details['fields'])) {
                $fields[$customKey]  = array(
                        'value' => $value
                    );
            }
        }
        
        $docLimit = $this->user->documentLimit;
        
        if ($docLimit === false) {
            // Applied tag when user is overlimit.
            $this->sendNotification("over_limit", " $document_name failed", 'Failed', [], $documentCreateLogRecord);
            $this->infusionSoftService->applyTagUsigTagId($user->infusionsoft_contact_id, 188);
            die("Document sent failed");
        }
        
        $document = $this->createAndSaveDocument($document_name, $templateID, $recipients, $tokens, $fields, $pricingTable, $request, $documentCreateLogRecord);
        usleep(10000000);
        
        if ($status != 0) {
            $document_status = $this->sendDocument($document->id, $docMessage);
            if ($document_status == "document.sent") {
                UserUsageDocs::updateOrCreate(['user_id' => $this->user->id], ['docs_sent' => $this->user->userUsageCount+1 ]);
                
                $message  = "Hello ".$this->user->first_name." ".$this->user->last_name."<br><br>";
                $message .= "\"$document_name\" Document sent successfully.<br>";
                $message .= "Thanks, <br>";
                $message .= "FusedDocs Team";
                //$this->sendEmail($this->user->email,"FusedDocs-PandaDoc Notifications", $message);
                $documentCreateLogRecord->status = 1;
                $documentCreateLogRecord->save();
                die("Document sent successfully");
            }
        } else {
            UserUsageDocs::updateOrCreate(['user_id' => $this->user->id], ['docs_sent' => $this->user->userUsageCount + 1 ]);
            
            $message  = "Hello ".$this->user->first_name." ".$this->user->last_name."<br><br>";
            $message .= "\"$document_name\" Document save successfully.<br>";
            $message .= "Thanks, <br>";
            $message .= "FusedDocs Team";
            //$this->sendEmail($this->user->email,"FusedDocs-PandaDoc Notifications", $message);
            $documentCreateLogRecord->status = 1;
            $documentCreateLogRecord->save();
            die("Document sent successfully to ");
        }
    }
    
    public function getrefereshedAcessToken($api_key, $fuseddoc_userID)
    {
        $user_panda_detail					= DocsAccountsPanda::where('api_key', $api_key)->where('user_id', $fuseddoc_userID)->first();

        if (count($user_panda_detail) && empty($user_panda_detail)) {
            $this->sendNotification("general", "No PandaDoc account exists for the given FuseKey. Please re-authorise your PandaDoc account, or check the FuseKey entered.", 'Failed');
            $message 				= $this->getMessage("No PandaDoc account exists For the given FuseKey. Please re-authorise your PandaDoc account, or check the FuseKey entered");
            $this->sendEmail($this->mails, "FusedDocs Error: Reauthorise Your PandaDoc Account.", $message);
            die('Requested FusedDocUserID doesnot exist.');
        }
        $access_token  						= $this->doRefereshAccessToken($user_panda_detail->refresh_token, $api_key, $fuseddoc_userID);
        return $access_token;
    }
    
    private function doRefereshAccessToken($referesh_token, $api_key, $fuseddoc_userID)
    {
        $post_flds 			= array(
    
            'client_id' 	=> env('PANDADOC_CLIENT_ID'),
            'client_secret'	=> env('PANDADOC_CLIENT_SECRET'),
            'refresh_token'	=> "$referesh_token",
            'scope'			=> env('PANDADOC_SCOPE'),
            'grant_type' 	=> 'refresh_token'
        );
        
        $url = "https://api.pandadoc.com/oauth2/access_token";
        $headers = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count($post_flds));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_flds);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        try {
            $response = curl_exec($ch);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors('Some problems have occured while refreshing your API access token. Please try again later.');
        }
        curl_close($ch);

        if (!$response) {
            $this->sendNotification("api_error", "Access token renewal has failed to no response from the PandaDoc API.", 'Failed');
            $message 				= $this->getMessage("Our system has failed to renew our access token for PandaDoc as their API returned a blank response. Please login to FusedDocs and re-authorise as soon as possible.");
            $this->sendEmail($this->mails, "FusedDocs Notification: PandaDoc API Access Error", $message);
            die('API to referesh access token failed to give response.');
        }
        $response_array = json_decode($response);

        if (!isset($response_array->access_token)) {
            $this->sendNotification("api_error", "Access token renewal has failed to an error response from the PandaDoc API.", 'Failed');
            $message 				= $this->getMessage("Our system has failed to renew our access token for PandaDoc as their API returned an error response. Please login to FusedDocs and re-authorise as soon as possible.");
            $this->sendEmail($this->mails, "FusedDocs Notification: PandaDoc API Access Error", $message);
            die('API to referesh access token failed');
        }

        $update				= array(
        'access_token'  	=>$response_array->access_token,
        'refresh_token'		=>$response_array->refresh_token
        );

        $user_panda_detail	= DocsAccountsPanda::where('api_key', $api_key)->where('user_id', $fuseddoc_userID)->update($update);

        return $response_array->access_token;
    }

    private function getTemplateDetail($templateID)
    {
        $template_details 		= array(
            'tokens' 			=> array(),
            'fields' 			=> array(),
            'name'				=> ""
        );
        $url 					= "https://api.pandadoc.com/public/v1/templates/".$templateID."/details";
        $post_flds				= array();
        $api_response 			= $this->curlRequestToEndPoint($url, $post_flds);
        
        if (!$api_response) {
            $this->sendNotification("api_error", "The PandaDoc API has failed to return your template details.", 'Failed');
            $message 				= $this->getMessage("The PandaDoc API has failed to return your template details.");
            $this->sendEmail($this->mails, "FusedDocs Notification: PandaDoc Template Details API Error", $message);
            die('Get Template detail API failed to give response.');
        }
        
        $response_array 		= json_decode($api_response);
        
        if (!isset($response_array->name)) {
            $this->sendNotification("api_error", "The PandaDoc API has failed to return your template details.", 'Failed');
            $message 				= $this->getMessage("The PandaDoc API has failed to return your template details.");
            $this->sendEmail($this->mails, "FusedDocs Notification: PandaDoc Template Details API Error", $message);
            die("Template detail API failed to give response");
        }

        if (isset($response_array->name)) {
            $template_details['name'] = $response_array->name;
        }
        
        if (isset($response_array->tokens) && count($response_array->tokens)) {
            foreach ($response_array->tokens as $token) {
                array_push($template_details['tokens'], $token->name);
            }
        }
        
        if (isset($response_array->fields) && count($response_array->fields)) {
            foreach ($response_array->fields as $field) {
                array_push($template_details['fields'], $field->name);
            }
        }
        
        if (isset($response_array->roles) && count($response_array->roles)) {
            $template_details['roles'] =  $response_array->roles;
        }
        
        return $template_details;
    }
    
    private function curlRequestToEndPoint($url, $post_flds)
    {
        $response 		= "";
        $url 			= $url;
        $headers = array('Authorization: Bearer '.$this->access_token,'Content-Type: application/json;charset=UTF-8');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if (count($post_flds)) {
            curl_setopt($ch, CURLOPT_POST, count($post_flds));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_flds));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        try {
            $api_response 				= curl_exec($ch);
        } catch (\Exception $exception) {
            return redirect()->back()->withErrors('A problem has occured during our API requests to PandaDoc. Please try again later.');
        }
        curl_close($ch);
        
        if ($api_response) {
            $response 		= $api_response;
        }

        return $response;
    }
    
    private function createAndSaveDocument($document_name, $templateID, $recipients, $tokens, $fields, $pricingTable, $fieldsLog, $documentCreateLogRecord)
    {
        $document					= array();
        $http_post_log              = array();
        $post_flds 					= array(
            'name' 				=>$document_name,
            'template_uuid'		=>$templateID,
            'recipients'		=>$recipients,
        );
        $http_post_log['recipients'] = array_keys($post_flds);
        
        if (count($tokens) > 0) {
            $post_flds["tokens"] = $tokens;
            $http_post_log['tokens'] = array_keys($tokens);
        }
        if (count($fields) > 0) {
            $post_flds["fields"] = $fields;
            $http_post_log['fields'] = $fieldsLog;
        }
        
        $prices = $this->infusionSoftService->fetchLastleadDetails($this->contact_id, $this->fuseddoc_userID, $this->app->id);
        
        if ($pricingTable == 1 && isset($prices[0]) && count($prices) > 0) {
            $rows = array();
            $discount = array();
            foreach ($prices as $price) {
                $discount[] = $price['ProductDiscountPrice'];
                
                $description = '';
                $pname = $price['ProductName'];
                
                if (isset($price['Cycle']) && !empty($price['Cycle'])) {
                    if ($price['Cycle'] == 1) {
                        $cycle = 'Year';
                    }
                    if ($price['Cycle'] == 2) {
                        $cycle = 'Month';
                    }
                    if ($price['Cycle'] == 3) {
                        $cycle = 'Week';
                    }
                    if ($price['Cycle'] == 6) {
                        $cycle = 'Day';
                    }
                    
                    $pname .= ' (Per '.$cycle.')';
                }
                
                $rows[] =  array(
                                "options" => array(
                                        "optional"=> true,
                                        "optional_selected"=> true,
                                        "qty_editable"=> true
                                    ),
                                "data" => array(
                                    "name" => $pname,
                                    "price" => $price['ProductPrice'],
                                    "qty" => $price['Qty']
                                            )
                                    );
            }
            $priceData = array(
                                "name" => "PricingTable1",
                                "sections" => array( array(
                                    "title" => "Section1",
                                    "default" => true,
                                    "rows" => $rows
                                    )
                                )
                            );
                            
            if (array_sum($discount) > 0) {
                $priceData["options"] = array(
                                        "currency" => "USD",
                                        "discount" => array(
                                            "value" => array_sum($discount),
                                            "type" => "absolute",
                                            "name" => "Global Discount"
                                        )
                                    );
            } else {
                $priceData["options"] = array("currency" => "USD");
            }
            
            $post_flds['pricing_tables'][] = $priceData;
        }
        
        $url						= "https://api.pandadoc.com/public/v1/documents";
        $api_response				= $this->curlRequestToEndPoint($url, $post_flds);
        
        if (!$api_response) {
            $this->sendNotification("api_error", "The PandaDoc CreateDocument API has failed to give a response.", 'Failed', $api_response, $documentCreateLogRecord);
            $message 				= $this->getMessage("There has been an error creating your document via the PandaDoc API as they have failed to responsed to our request.");
            
            $this->sendEmail($this->mails, "FusedDocs Error: Your Document Was Not Created By PandaDoc", $message);
            die('Create Document API failed to give response.');
        }
        $api_response_array			= json_decode($api_response);
        
        if (!isset($api_response_array->id)) {
            $this->sendNotification("api_error", "The PandaDoc CreateDocument API has failed to give a response.", 'Failed', $api_response, $documentCreateLogRecord);
            //$message 				= $this->getMessage("Create Document API failed to give response.");
            $message = $this->pandaErrorMessage($api_response);
            $this->sendEmail($this->mails, "FusedDocs Error: Your Document Was Not Created By PandaDoc", $message);
            die('Create Document API failed'."Create Document API failed");
        }
        
        $document = $api_response_array;
        
        //After successfully created document in pandadoc
        $fieldsLog = (array) $fieldsLog;
        $fieldLogs = array_keys($fieldsLog);
        $docData = array(
                'user_id'  =>  $this->user->id,
                'infs_account_id'  =>  $this->app->id,
                'document_id'  =>  $document->id,
                'template_id'  =>  $this->temp_id,
                'http_post_log'  =>  json_encode($fieldLogs),
                'contactId'  => $this->contact_id,
                'type'  => 'pandadoc'
            );
        DocsCompleted::create($docData);
        
        return $document;
    }
    
    private function sendDocument($documentID, $message = '')
    {
        $document_status            = array();
        $url 						="https://api.pandadoc.com/public/v1/documents/".$documentID."/send";
        $post_flds 					= array(
    
                'message' 			=>$message,
                'silent'			=>false
        );
        
        $api_response				= $this->curlRequestToEndPoint($url, $post_flds);
        
        if (!$api_response) {
            $this->sendNotification("api_error", "The PandaDoc Send Document API has failed to give a response.", 'Failed');
            $message 				= $this->getMessage("There has been an error sending your document via the PandaDoc API as they have failed to responsed to our request.");
            $this->sendEmail($this->mails, "FusedDocs Error: Your Document Was Not Sent By PandaDoc", $message);
            die('Send Document API failed to give response.');
        }
        
        $api_response_array			= json_decode($api_response);
        
        
        
        if (!isset($api_response_array->status)) {
            $this->sendNotification("api_error", "The PandaDoc Send Document API has failed to give a response.", 'Failed');
            $message 				= $this->getMessage("There has been an error sending your document via the PandaDoc API as they have failed to responsed to our request.");
            $this->sendEmail($this->mails, "FusedDocs Error: Your Document Was Not Sent By PandaDoc", $message);
            die("Send Document API failed");
        }

        $document_status 				= $api_response_array->status;
        
        return $document_status;
    }
    
    private function sendNotification($error_type, $message, $status, $document_data = '', $documentCreateLogRecord = false)
    {
        $insert							=  array(
            'user_id'					=> @$this->fuseddoc_userID,
            'error_type' 				=> $error_type,
            'message'					=> $message,
            'template_id'				=> $this->temp_id,
            'contactId'                 => $this->contact_id,
            'status'                    => $status
        );
        
        if (isset($this->app->id)) {
            $insert['infs_account_id'] = $this->app->id;
        }
        if (!empty($document_data)) {
            $insert['document_data'] = json_encode($document_data);
        }
        if (!empty($this->roles)) {
            $insert['role_data'] = json_encode($this->roles);
        }
        DocsHttpErrors::create($insert);
        if ($documentCreateLogRecord) {
            $documentCreateLogRecord->logError($message);
        }
    }
    
    private function getMessage($message)
    {
        $content  = "<h1>Hello ".$this->user->first_name."</h1>";
        $content .= "<p>An error has occurred while generating and sending your PandaDoc.</p>";
        $content .= "<p>Error Message: ".$message."</p><br>";
        $content .= "<p>Thanks, </p>";
        $content .= "<p>FusedDocs Team</p>";
        
        return $content;
    }
    
    private function pandaErrorMessage($data)
    {
        $erroData = json_decode($data);
        
        $message  = "<h2>Hi ".$this->user->first_name."</h2>";
        $message .= "<p>An error has occurred while generating and sending your PandaDoc.</p><br>";
        
        if (isset($erroData->type)) {
            $message .= "<h3>".ucfirst(str_replace('_', ' ', $erroData->type))."</h3>";
        }
        $message .= "<ol>";
        if (isset($erroData->detail->recipients)) {
            foreach ($erroData->detail->recipients as $value) {
                $message .= "<li>".$value."</li>";
            }
        }
        if (isset($erroData->detail->message)) {
            $message .= "<li>".$erroData->detail->message."</li>";
        }
        $message .= "</ol><br>";
        $message .= "<p>Thanks, </p>";
        $message .= "<p>FusedDocs Team</p>";
        return $message;
    }
    
    private function fuseUserMessage()
    {
        $message  = "<h2>Hello ".$this->user->first_name."</h2>";
        $message .= "<p>We have recently updated the identifier used when sending posts from Infusionsoft to FusedDocs, and it seems your post is still using the old identifier.</p>";
        $message .= "<p>In short, we have removed the field 'FuseUser' and changed your value for 'FuseKey' to (".$this->user->fusekey.").</p>";
        $message .= "<p>We have still sent your document, but please update your HTTP posts, removing 'FuseUser' and updating your fusekey value to (".$this->user->fusekey.").</p>";
        $message .= "<p>Detailed setup instructions can be found by logging into your FusedDocs account <a href='".url("/manage-panda-account")."'>here</a>.</p><br>";
        $message .= "<p>Thanks, </p>";
        $message .= "<p>FusedDocs Team</p>";
        return $message;
    }
}
