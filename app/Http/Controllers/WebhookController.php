<?php

namespace App\Http\Controllers;

use App\Services\InfusionSoftService;
use App\Services\UserDocumentService;
use App\Services\UserInfusionSoftService;
use App\Services\DocusignService;
use App\Http\Requests;
use App\User;
use App\InfsAccount;
use App\DocsAccountsPanda;
use App\DocsHttpErrors;
use App\DocsTagSettings;
use App\DocsCompleted;
use App\DocsHistory;
use App\DocsAccountsDocsign;
use App\DocumentAdditionalInfsFields;
use App\DocumentStageSetting;
use App\DocsWebhookLog;
use Illuminate\Http\Request;
use Infusionsoft\Token as InfusionsoftToken;
use Carbon\Carbon;
use DB;
use Validator;
use Infusionsoft;
use Session;
use Auth;

class WebhookController extends Controller
{
    private $authUser 	 = '';
    private $infusionsoft 	 = '';
    protected $infusionSoftService = '';
    protected $userDocumentService ;
    protected $docusignService ;

    public function __construct(InfusionSoftService $infusionSoftService, UserDocumentService $userDocumentService, DocusignService $docusignService)
    {
        //$this->middleware('infusionsoft');
        $this->infusionSoftService = $infusionSoftService;
        $this->userDocumentService = $userDocumentService;
        $this->docusignService = $docusignService;
    }

    /**
    * Perform logging, applying tags, etc upon pandadoc document change event. Sent via webhook
    *
    * @return \Illuminate\Http\Response
    */
    public function processPandaWebhook(Request $request)
    {
        $data                   = $request->all();
        $data                   = json_encode($data);
        $response	            = json_decode($data);
        $user_id = $status = $contact_id = $infs_id = $completedId = '';
        $webhookLog = DocsWebhookLog::create(['data' => $data, 'status'=> '0', 'type' => 'pandadoc']);
        $tagId = 0;

        foreach ($response as $res) {
            if (is_array(json_decode(json_encode($res), true))) {
                $status = $res->data->status;
                $temp_name = $res->data->name;
                $temp_id = $res->data->id;
                $dateCreated = $res->data->date_created;
                $totalRevenue = $res->data->grand_total->amount;

                if (!empty($status)) {
                    $status_arr = explode('.', $status);
                    $status = $status_arr[1];
                }
            
                $client_temp_settings = "";

                if (!empty($status) && !empty($temp_name)) {
                    $documentData = DocsCompleted::where('document_id', $temp_id)->first();
                    if (!$documentData) {
                        $webhookLog->logError("No document data stored!");
                        return response('OK', 200)->header('Content-Type', 'text/plain');
                    }
                    $contact_id = $documentData->contactId;
                    $user_id = $documentData->user_id;
                    $infs_id = $documentData->infs_account_id;
                    $completedId = $documentData->id;
                    
                    if ($documentData && isset($documentData->template_id)) {
                        $templateId =  $documentData->template_id;
                    } else {
                        $webhookLog->logError("No document data stored!");
                        return response('OK', 200)->header('Content-Type', 'text/plain');
                    }
                    
                    $client_temp_settings = DocsTagSettings::where('template_id', $templateId)->where('document_status', $status)->first();
                        
                          
                    if (empty($client_temp_settings)) {
                        $webhookLog->logError("No document tag settings stored!");
                        return response('OK', 200)->header('Content-Type', 'text/plain');
                    }

                    $user = User::where('id', $user_id)->first();
                    if (isset($user->role_id) && $user->role_id == 1) {
                        $infusionsoft = new Infusionsoft\Infusionsoft(array(
                            'clientId' => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
                            'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
                            'redirectUri' => env('INFUSIONSOFT_REDIRECT_URI'),
                        ));
                    } else {
                        $infusionsoft = new Infusionsoft\Infusionsoft(array(
                            'clientId' => env('INFUSIONSOFT_CLIENT_ID'),
                            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                            'redirectUri' => env('INFUSIONSOFT_REDIRECT_URI'),
                        ));
                    }
                    $client_is_account = InfsAccount::where('id', $documentData->infs_account_id)->where('user_id', $user_id)->first();
                    
                    $access_token = $client_is_account->access_token;
                    $infusionsoft->setToken(new InfusionsoftToken([
                        'access_token' => $client_is_account->access_token,
                        'refresh_token' => $client_is_account->referesh_token,
                        'expires_in' => Carbon::parse($client_is_account->expire_date)->timestamp
                    ]));
                    
        
                    if ($infusionsoft->getToken()) {
                        try {
                            //nothing
                        } catch (\Infusionsoft\TokenExpiredException $e) {
                            $infusionsoft->refreshAccessToken();
                        }
                    }

                    $this->updateStage($infusionsoft, $templateId, $contact_id, $status);
                    //apply data to infusionsoft contact;
                    if ($status == 'sent') { //if status is sent, update infs choosen fields
                        $this->updateInfsFields($infusionsoft, $templateId, $contact_id, $dateCreated, $totalRevenue);
                    }
                    
                    if ($contact_id) {
                        $tag_status = $this->applyTagToContact($infusionsoft, $contact_id, $client_temp_settings->applied_tag_id);
                        if ($tag_status == 1) {
                            $tagId = $client_temp_settings->applied_tag_id;
                        }
                    }
                }
            }
        }
        
        if (!empty($user_id) && !empty($contact_id)) {
            $webhookLog->user_id =  $user_id;
            $webhookLog->infs_account_id =  $infs_id;
            $webhookLog->completed_id =  $completedId;
            $webhookLog->document_status =  $status;
            $webhookLog->contactId =  $contact_id;
            $webhookLog->tag_applied =  $tagId;
            $webhookLog->status =  1;
            $webhookLog->save();
        } else {
            $webhookLog->logError("No user or contact ID!");
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }
    }

    /**
    * Perform logging, applying tags, etc upon docusign document change event. Sent via webhook
    *
    * @return \Illuminate\Http\Response
    */
    public function processDocusignWebhook(Request $request)
    {
        $data = file_get_contents('php://input');

        $userId = $status = $contactId = $infsId = $completedId = $templateId = '';
        $tagId = 0;
        $webhookLog = DocsWebhookLog::create(['data' => $data, 'status'=> '0', 'type' => 'docusign']);
        
        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_PARSEHUGE);
        
        $envelopeId = (string)$xml->EnvelopeStatus->EnvelopeID ?? false;
        $status = $xml->EnvelopeStatus->Status ?? false;
        $dateCreated =  Date("Y-m-d h:i:s");

        if (!$envelopeId || !$status) {
            $webhookLog->logError("No required fiels not found!");
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $documentData = DocsCompleted::where('document_id', $envelopeId)->first();
        if (!$documentData) {
            $webhookLog->logError("No document data stored!");
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }
        $contactId = $documentData->contactId;
        $userId = $documentData->user_id;
        $infsId = $documentData->infs_account_id;
        $completedId = $documentData->id;
        $templateId = $documentData->template_id;



        $clientTempSettings = DocsTagSettings::where('template_id', $templateId)->where('document_status', $status)->first();
                        
                          
        if (empty($clientTempSettings)) {
            $webhookLog->logError("No document tag settings stored!");
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }

        $user = User::where('id', $userId)->first();
        if (isset($user->role_id) && $user->role_id == 1) {
            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId' => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
                'redirectUri' => env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        } else {
            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId' => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri' => env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        }
        $clientISAccount = InfsAccount::where('id', $documentData->infs_account_id)->where('user_id', $userId)->first();
        
        $access_token = $clientISAccount->access_token;
        $infusionsoft->setToken(new InfusionsoftToken([
            'access_token' => $clientISAccount->access_token,
            'refresh_token' => $clientISAccount->referesh_token,
            'expires_in' => Carbon::parse($clientISAccount->expire_date)->timestamp
        ]));




        //apply tags
        //update opportunity
        //write to infs
        

        if ($contactId) {
            $tag_status = $this->applyTagToContact($infusionsoft, $contactId, $clientTempSettings->applied_tag_id);
            if ($tag_status == 1) {
                $tagId = $clientTempSettings->applied_tag_id;
            }

            $this->updateStage($infusionsoft, $templateId, $contactId, $status);

            if ((string)$xml->EnvelopeStatus->Status === "Sent") {
                $this->updateInfsFields($infusionsoft, $templateId, $contactId, $dateCreated, '');
            }
        }

        $webhookLog->user_id =  $userId;
        $webhookLog->infs_account_id =  $infsId;
        $webhookLog->completed_id =  $completedId;
        $webhookLog->document_status =  $status;
        $webhookLog->contactId =  $contactId;
        $webhookLog->tag_applied =  $tagId;
        $webhookLog->status =  1;
        $webhookLog->save();
    }
    
    /**
    * Update INFS stage based on sent webhook status of the document
    *
    * @return Boolean, true for success false for unsuccessful or error.
    */
    protected function updateStage($infusionsoft, $templateId, $contactId, $docStatus)
    {
        //get settings data
        $result = DocumentStageSetting::where('temp_id', $templateId)->where('document_status', $docStatus)->first();
        if (!$result) {
            return false;
        }

        $stageId = $result['infs_opportunity_stage'];

        //query most recent opportunity
        $query = array('ContactID' => $contactId);
        $return_fields = array('Id');
        $infsLeadResult = $infusionsoft->data()->query("Lead", 1, 0, array("ContactId"=>$contactId), array("Id"), "LastUpdated", false);
        if (!isset($infsLeadResult[0]['Id']) && $result['create_opp_if_not_exists'] == 1) { //create opportunity if no opportunity and client set it
            $result = $infusionsoft->data()->add('Lead', array(
                'ContactID'               => $contactId,
                'StageID'                 => $stageId,
                'OpportunityTitle'        => ""
            ));
            return true;
        } elseif (isset($infsLeadResult[0]['Id'])) {
            $leadId = $infsLeadResult[0]['Id'];
            $result = $infusionsoft->data()->update('Lead', $leadId, array('StageID' => $stageId));

            return true;
        } else {
            return true;
        }
    }


    private function updateInfsFields($infusionsoft, $templateId, $contactId, $dateCreated, $totalRevenue)
    {
        $clientAdditionalFields = DocumentAdditionalInfsFields::where('temp_id', $templateId)->get();
        $fieldsToUpdateContact = [];
        $fieldsToUpdateOpp = [];
        $leadId = "";
        $infsResult = "";
        foreach ($clientAdditionalFields as $clientAdditionalField) {
            if ($clientAdditionalField['type'] == 'document_sent_date') {
                if (!empty($clientAdditionalField['contact_field'])) {
                    $fieldsToUpdateContact[$clientAdditionalField['contact_field']] = $dateCreated;
                }
                if (!empty($clientAdditionalField['opportunity_field'])) {
                    $fieldsToUpdateOpp[$clientAdditionalField['opportunity_field']] = $dateCreated;
                }
            } elseif ($clientAdditionalField['type'] == 'total_document_revenue') {
                if (!empty($clientAdditionalField['contact_field'])) {
                    $fieldsToUpdateContact[$clientAdditionalField['contact_field']] = $totalRevenue;
                }
                if (!empty($clientAdditionalField['opportunity_field'])) {
                    $fieldsToUpdateOpp[$clientAdditionalField['opportunity_field']] = $totalRevenue;
                }
            }
        }

        if (!empty($fieldsToUpdateContact)) {
            $infsResult = $infusionsoft->contacts('xml')->update($contactId, $fieldsToUpdateContact);
        }
        if (!empty($fieldsToUpdateOpp)) {
            $infsLeadResult = $infusionsoft->data()->query("Lead", 1, 0, array("ContactId"=>$contactId), array("Id"), "LastUpdated", false);
            $leadId = isset($infsLeadResult[0]['Id']) ? $infsLeadResult[0]['Id'] : false;
            if ($leadId !== false) {
                $infsResult = $infusionsoft->data()->update("Lead", $leadId, $fieldsToUpdateOpp);
            }
        }
    }

    private function applyTagToContact($infusionsoft, $contact_id, $tagID)
    {
        $contactId  = $contact_id;
        $tagId      = $tagID;
        
        $result     = $infusionsoft->contacts('xml')->addToGroup($contactId, $tagId);
        return $result;
    }


    public function getIsAccount($user)
    {
        return \CommanHelper::infsTokenByUserId($user);
    }
}
