<?php

namespace App\Http\Controllers;

use URL;
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
use Illuminate\Http\Request;
use Infusionsoft\Token as InfusionsoftToken;
use Carbon\Carbon;
use DB;
use Validator;
use Infusionsoft;
use Session;
use Auth;

class DocsController extends Controller
{
    private $authUser 	 = '';
    private $infusionsoft 	 = '';
    protected $infusionSoftService = '';
    protected $userDocumentService ;
    protected $docusignService ;

    public function __construct(InfusionSoftService $infusionSoftService, UserDocumentService $userDocumentService, DocusignService $docusignService)
    {
        $this->middleware('auth');
        $this->middleware('infusionsoft');
        $this->authUser = Auth::user();
        $this->infusionSoftService = $infusionSoftService;
        $this->userDocumentService = $userDocumentService;
        $this->docusignService = $docusignService;
    }

    /**
    * Redirect to dashboard
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function redirectdashboard()
    {
        session(['app_type' => 'docs']);
        return redirect('/dashboard');
    }


    public function index()
    {
        return view('docshome');
    }

    /**
    * Show pandadocs page and templates
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function pandadocs(Request $request)
    {
        $userID 		= $this->authUser->id;
        $account 		= DocsAccountsPanda::where('user_id', $userID)->first();
        $templates = $this->listTemplates($request);
        return view('docs/pandadocs', compact('account', 'templates'));
    }

    /**
    * Show docusign page and template
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function docusign()
    {
        //var_dump($this->docusignService->createDocumentFromTemplate("", "b8f0315f-401a-4db9-afa2-4fdd900c579f"));exit;

        $account = DocsAccountsDocsign::where('user_id', $this->authUser->id)->first();
        
        if (!$account || isset($account->expires_date) && $account->expires_date < Carbon::now()) {
            return redirect('/manageaccounts');
        }
        $templates = $this->docusignService->getTemplates($account);
        return view('docs.docusign', compact('account', 'templates'));
    }
    

    /**
    * Show pandadocs template configuration
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function getTemplateDetailsPandadocs(Request $request)
    {
        $inputs = $request->all();
        $inputs['user_id'] = $this->authUser->id;

        $validator = Validator::make($inputs, $this->getTemplateRule());
        if ($validator->fails()) {
            die(implode(" ", $validator->errors()->all()));
        }
        
        $tokens = array();
        $fields = array();
        $roles = array();
        $duplicate_fields = array();
        $temp_name = "";
        $accountId = $inputs['account_id'];
        $userID = $this->authUser->id;
        $templateID = $request->tempID;
        
        $account = DocsAccountsPanda::where('user_id', $userID)->first();
        $access_token = $account->access_token;

        $url = "https://api.pandadoc.com/public/v1/templates/".$templateID."/details";
        $post_flds = array();
        $api_response = $this->curlRequestToEndPoint($url, $post_flds, $access_token);
        
        if (!$api_response) {
            die("Pandadocs  API Error, no response!");
        }

        $api_response_array = json_decode($api_response);
        
        if (!isset($api_response_array->tokens) || !isset($api_response_array->fields)) {
            die("Pandadocs API Error, Invalid token!");
        }
            
        $hiddenTokens = array( 'Client.CompanyName','Document.SeqNumber' );
        if (count($api_response_array->tokens)) {
            foreach ($api_response_array->tokens as $token) {
                if (isset($token->value) && $token->value != '') {
                    continue;
                }
                if (in_array($token->name, $hiddenTokens)) {
                    continue;
                }
                $tokens[] = $token->name;
            }
        }
        
        $hiddenFields = array( 'Signature' );
        if (count($api_response_array->fields)) {
            foreach ($api_response_array->fields as $field) {
                if (isset($field->value) && $field->value != new \stdClass()) {
                    continue;
                }
                if (in_array($field->name, $hiddenFields)) {
                    continue;
                }
                if (in_array($field->name, $fields)) {
                    $duplicate_fields[] = $field->name;
                }
                
                $fields[] = $field->name;
            }
        }

        if (isset($api_response_array->roles) && count($api_response_array->roles)) {
            foreach ($api_response_array->roles as $role) {
                $roles[] = $role->name;
            }
        }


        $temp_name = $api_response_array->name;
        $created_by = $api_response_array->created_by->email;

        $temp_tag_settings = DocsTagSettings::where('user_id', $userID)->where('infs_account_id', $accountId)->where('template_id', $templateID)->pluck('applied_tag_id', 'document_status');
        $selected_IS_tags = $this->getTags($accountId);

        $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
            'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ));

        $infusionsoftAccounts = $this->getIsAccount($userID);
        $clientAccount = $this->setTokenClient($infusionsoftAccounts['id']);


        $stages = $this->infusionsoft->data()->query("Stage", 1000, 0, ['Id' =>'%'], ['Id', 'StageName'], 'StageOrder', true);
        $query_fields = ['FormId' => [-1, -4]];
        $customFields  = $this->infusionsoft->data()->query("DataFormField", 1000, 0, $query_fields, ['Id', 'Label', 'Name', 'FormId'], 'Name', true);

        $contactFields = $this->getInfusionsoftContactFields();
        $opportunityFields = $this->getInfusionsoftOpportunityFields();
        $infsFields = [];
        
        foreach ($customFields as $customField) { //prepend underscore since it is custom field
            $infsFields[] = ['Value' => "_".$customField['Name'], 'Name' => $customField['Label'], 'FormId' => $customField['FormId'], 'type' => 'customField'];
        }
        foreach ($contactFields as $contactField) {
            $infsFields[] = ['Value' => $contactField['Value'], 'Name' => $contactField['Name'], 'FormId' => -1,  'type' => 'regularField', ];
        }
        foreach ($opportunityFields as $opportunityField) {
            $infsFields[] = ['Value' => $opportunityField['Value'], 'Name' => $opportunityField['Name'], 'FormId' => -4,  'type' => 'regularField', ];
        }

        $documentAdditionalInfsFieldsDocSentDate = DocumentAdditionalInfsFields::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->where('type', 'document_sent_date')->first();
        $documentAdditionalInfsFieldsDocRevenue = DocumentAdditionalInfsFields::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->where('type', 'total_document_revenue')->first();
        $documentStageSettingObj = DocumentStageSetting::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->get();
        $documentStageSettings = [];
        $createNewOppIfNotExists = false;
        foreach ($documentStageSettingObj as $data) {
            $documentStageSettings[$data['document_status']] = $data['infs_opportunity_stage'];
            $createNewOppIfNotExists = $data['create_opp_if_not_exists'] == 1 ? true : false;
        }
        
        $document_status  = ['draft','sent','viewed','completed','voided','rejected'];
        
        return view('docs/_get_template_details_pandadocs', compact(
            'tokens',
            'fields',
            'account',
            'duplicate_fields',
            'templateID',
            'roles',
            'temp_name',
            'document_status',
            'temp_tag_settings',
            'selected_IS_tags',
            'stages',
            'customFields',
            'infsFields',
            'documentAdditionalInfsFieldsDocSentDate',
            'documentAdditionalInfsFieldsDocRevenue',
            'documentStageSettings',
            'createNewOppIfNotExists'
        ))->render();
    }

    /**
    * Show docusign template configuration
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function getTemplateDetailsDocusign(Request $request)
    {
        $params = $request->all();
        if (isset($params['tempID'])) {
            $inputs = $request->all();
            $tokens = [];
            $fields = [];
            $roles = [];
            $duplicate_fields = [];
            $temp_name = "";
            $userID = $this->authUser->id;
    
            $templateID = $params['tempID'];
            $accountId = $params['account_id'];
            $templateDetails = $this->docusignService->getTemplateDetails($params['tempID']);
            
            if (!isset($templateDetails['envelope_template_definition'])) {
                return 'error';
            }
            
            $allTabs = config('docuSign.fields');
            $tabs = array();
            /*echo "<pre>";
            var_dump($templateDetails['recipients']['signers']);exit;*/
            \Log::info('RECIPIENTS: '.print_r($templateDetails['recipients'], true));
            if (isset($templateDetails['recipients']) && count($templateDetails['recipients']) > 0) {
                foreach ($templateDetails['recipients']['signers'] as $recipient) {
                    foreach ($allTabs as $tab) {
                        if (count($recipient['tabs'][$tab]) > 0) {
                            foreach ($recipient['tabs'][$tab] as $name) {
                                if (strpos(strtolower($name['tab_label']), "signature") === false) {
                                    if (!isset($tabs[$name['tab_label']])) {
                                        $tabs[$name['tab_label']] = 1;
                                    } else {
                                        $tabs[$name['tab_label']]++;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $temp_name = $templateDetails['envelope_template_definition']['name'];
            $temp_tag_settings          = DocsTagSettings::where('user_id', $userID)->where('infs_account_id', $accountId)->where('template_id', $templateID)->pluck('applied_tag_id', 'document_status');
            
            $selected_IS_tags = $this->getTags($accountId);
            
            $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
            
            $infusionsoftAccounts = $this->getIsAccount($userID);
            $clientAccount = $this->setTokenClient($infusionsoftAccounts['id']);


            $stages = $this->infusionsoft->data()->query("Stage", 1000, 0, ['Id' =>'%'], ['Id', 'StageName'], 'StageOrder', true);
            $query_fields = ['FormId' => [-1, -4]];
            $customFields  = $this->infusionsoft->data()->query("DataFormField", 1000, 0, $query_fields, ['Id', 'Label', 'Name', 'FormId'], 'Name', true);

            $contactFields = $this->getInfusionsoftContactFields();
            $opportunityFields = $this->getInfusionsoftOpportunityFields();
            $infsFields = [];
            
            foreach ($customFields as $customField) { //prepend underscore since it is custom field
                $infsFields[] = ['Value' => "_".$customField['Name'], 'Name' => $customField['Label'], 'FormId' => $customField['FormId'], 'type' => 'customField'];
            }
            foreach ($contactFields as $contactField) {
                $infsFields[] = ['Value' => $contactField['Value'], 'Name' => $contactField['Name'], 'FormId' => -1,  'type' => 'regularField', ];
            }
            foreach ($opportunityFields as $opportunityField) {
                $infsFields[] = ['Value' => $opportunityField['Value'], 'Name' => $opportunityField['Name'], 'FormId' => -4,  'type' => 'regularField', ];
            }

            $documentAdditionalInfsFieldsDocSentDate = DocumentAdditionalInfsFields::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->where('type', 'document_sent_date')->first();
            $documentAdditionalInfsFieldsDocRevenue = DocumentAdditionalInfsFields::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->where('type', 'total_document_revenue')->first();
            $documentStageSettingObj = DocumentStageSetting::where('user_id', $this->authUser->id)->where('temp_id', $templateID)->get();
            $documentStageSettings = [];
            $createNewOppIfNotExists = false;
            foreach ($documentStageSettingObj as $data) {
                $documentStageSettings[$data['document_status']] = $data['infs_opportunity_stage'];
                $createNewOppIfNotExists = $data['create_opp_if_not_exists'] == 1 ? true : false;
            }

            $document_status  = ['Sent','Delivered','Signed','Completed','Declined','Voided'];

            return view('docs._get_template_details_docusign', compact(
                'tabs',
                'duplicate_fields',
                'templateID',
                'temp_name',
                'document_status',
                'temp_tag_settings',
                'selected_IS_tags',
                'stages',
                'customFields',
                'infsFields',
                'documentAdditionalInfsFieldsDocSentDate',
                'documentAdditionalInfsFieldsDocRevenue',
                'documentStageSettings',
                'createNewOppIfNotExists'
            ))->render();
        }
    }


    /**
    * Saves the INFS tag to db in pandadocs
    *
    * @return \Illuminate\Http\Response .
    */
    public function saveTagSelections(Request $request)
    {
        $params = $request->all();
        $tempId = $params['temp_id'];
        $data = array();
        $accountId = $params['account_id'];
        
        if ($tempId) {
            $tags = [];
            if ($params['type'] == 'pandadoc') {
                $tags = ['draft','sent','viewed','completed','voided','rejected'];
            } elseif ($params['type'] == 'docusign') {
                $tags = ['Sent','Delivered','Signed','Completed','Declined','Voided'];
            }
            foreach ($tags as $tag) {
                if (isset($params[$tag])) {
                    $checkIf = DocsTagSettings::where('user_id', $this->authUser->id)->where('infs_account_id', $accountId)->where('template_id', $tempId)->where('document_status', $tag)->first();
                    
                    if (!empty($checkIf)) {
                        DocsTagSettings::where('id', $checkIf->id)->update(['applied_tag_id' => $params[$tag] ]);
                    } else {
                        DocsTagSettings::create(['user_id' => $this->authUser->id,'infs_account_id' => $accountId, 'template_id' => $tempId, 'document_status' => $tag, 'applied_tag_id' => $params[$tag] ]);
                    }
                }
            }
            $data['success'] = 'Tags are saved.';
        } else {
            $data['fail'] = 'Template id not found';
        }
        return $data;
    }

    /**
    * Retrieve all the tags from authed INFS
    *
    * @return \Illuminate\Http\Response .
    */
    public function getTagsFromISAccount(Request $request)
    {
        $IS_account_id = $request->IS_account_id;
        $tags = array();
        $temp_id = $request->temp_id;
        
        if ($IS_account_id) {
            // get all tags from infusionsoft account
            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId' => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri' => env('INFUSIONSOFT_REDIRECT_URI'),
            ));
            $client_is_account = InfsAccount::where('id', $IS_account_id)->first();
            $infusionsoft->setToken(new InfusionsoftToken([
                'access_token' => $client_is_account->access_token,
                'refresh_token' => $client_is_account->referesh_token,
                'expires_in' => Carbon::parse($client_is_account->expire_date)->timestamp
            ]));

            if ($infusionsoft->getToken() && $client_is_account->expires_in!= -1) {
                try {
                    //nothing
                } catch (\Infusionsoft\TokenExpiredException $e) {
                    $infusionsoft->refreshAccessToken();
                }
            } else {
                $infusionsoft->refreshAccessToken();
            }
            $query_fields = array('GroupName' =>'%');
            $returnFields = array('Id','GroupName');
            $tags_arr = $infusionsoft->data()->query("ContactGroup", 1000, 0, $query_fields, $returnFields, 'Id', true);
            
            $final_arr = array();
            
            if (count($tags_arr)) {
                foreach ($tags_arr as $arr) {
                    $data['id'] = $arr['Id'];
                    $data['GroupName'] = isset($arr['GroupName']) ? $arr['GroupName']: '';
                    $final_arr[$data['id']] = $data['GroupName'];
                }
            }
            $tags['all']     = $final_arr;
        }
        $tags['saved'] = DocsTagSettings::where('user_id', $this->authUser->id)->where('template_id', $temp_id)->pluck('applied_tag_id', 'document_status')->toArray();
        
        return json_encode($tags);
    }

    /**
    * Show a static page on how to setup docs
    *
    * @return \Illuminate\Http\Response .
    */
    public function setupViewPandadocs()
    {
        $user = \Auth::user();
        return view('docs.pandadoc_setup', compact('user'));
    }

    /**
    * Shows the user document history page of current user
    *
    * @return \Illuminate\Http\Response .
    */
    public function listUserDocHistory()
    {
        $notifications = array();
        $userID = $this->authUser->id;

        $response = DocsHistory::where('user_id', $userID)->orderBy('id', 'created_at')->get();
        
        if (count($response)) {
            $notifications = $response;
        }
        return view('docs.list_history', compact('notifications'));
    }

    /**
    * Shows the error log page
    *
    * @return \Illuminate\Http\Response .
    */
    public function listPandaUserNotifications()
    {
        $notifications = array();
        $userID = $this->authUser->id;
        $update_notty = DocsHttpErrors::where('user_id', $userID)->update(['status' => 1]);
        
        $response = DocsHttpErrors::where('user_id', $userID)->orderBy('id', 'error_type')->get();
        
        if (count($response)) {
            $notifications = $response;
        }
        return view('docs.list_notifications', compact('notifications'));
    }

    /**
    * Saves additional
    *
    * @return \Illuminate\Http\Response .
    */
    public function saveAdditionalOptions(Request $request)
    {
        $docFields = ($request->input('saveDocFields'));
        $mostMostRecentOpportunity = ($request->input('mostMostRecentOpportunity'));
        $checkedAtLeastOne = false;
        $tempId = $request->input('temp_id');
        $createOppIfNotExists = $request->input('create_opp_if_not_exists');
        $createOppIfNotExists = $createOppIfNotExists ?? 0;
        
        //var_dump($request->all());exit;
        if (!$tempId) {
            return ['fail' => 'Template id not found'];
        }
        if ($docFields == 1) { //docfields checkbox ticked, save the data
            $checkedAtLeastOne = true;
            $infsFields = $request->input('infsField');

            foreach ($infsFields as $type => $infsField) {
                $dataExists = DocumentAdditionalInfsFields::where('user_id', $this->authUser->id)->where('temp_id', $tempId)->where('type', $type)->first();
                if (!empty($dataExists)) {
                    DocumentAdditionalInfsFields::where('id', $dataExists->id)->update(['contact_field' => $infsField['contact_field'], 'opportunity_field' => $infsField['opportunity_field'] ]);
                } else {
                    DocumentAdditionalInfsFields::create(['user_id' => $this->authUser->id, 'temp_id' => $tempId, 'type' => $type, 'contact_field' => $infsField['contact_field'], 'opportunity_field' => $infsField['opportunity_field'] ]);
                }
            }
        }

        if ($mostMostRecentOpportunity == 1) {
            $checkedAtLeastOne = true;
            $stageStatus = $request->input('stage_status');

            foreach ($stageStatus as $status => $value) {
                $dataExists = DocumentStageSetting::where('user_id', $this->authUser->id)->where('temp_id', $tempId)->where('document_status', $status)->first();
                if (!empty($dataExists)) {
                    DocumentStageSetting::where('id', $dataExists->id)->update(['infs_opportunity_stage' => $value, 'create_opp_if_not_exists' => $createOppIfNotExists]);
                } else {
                    DocumentStageSetting::create(['user_id' => $this->authUser->id, 'temp_id' => $tempId, 'document_status' => $status, 'infs_opportunity_stage' => $value, 'create_opp_if_not_exists' => $createOppIfNotExists]);
                }
            }
        }

        if (!$checkedAtLeastOne) {
            return ['fail' => 'Nothing to save'];
        }

        return ['success' => 'Success'];
    }


    /**
    * Build an array of rules for server-side validation.
    *
    * @return array of laravel rules.
    */
    protected function getTemplateRule()
    {
        return  [
              "account_id" => "required",
              "tempID" => "required",
              "user_id" => "required|exists:docs_accounts_panda,user_id",
        ];
    }

    /**
    * Build an array Infusionsoft Contact Field label and corresponding fieldname.
    *
    * @return array of laravel rules.
    */
    protected function getInfusionsoftContactFields()
    {
        return [['Value' => 'AccountId', 'Name' => 'Account Id'],['Value' => 'Address1Type', 'Name' => 'Address1 Type'],['Value' => 'Address2Street1', 'Name' => 'Address2 Street1'],['Value' => 'Address2Street2', 'Name' => 'Address2 Street2'],['Value' => 'Address2Type', 'Name' => 'Address2 Type'],['Value' => 'Address3Street1', 'Name' => 'Address3 Street1'],['Value' => 'Address3Street2', 'Name' => 'Address3 Street2'],['Value' => 'Address3Type', 'Name' => 'Address3 Type'],['Value' => 'Anniversary', 'Name' => 'Anniversary'],['Value' => 'AssistantName', 'Name' => 'Assistant Name'],['Value' => 'AssistantPhone', 'Name' => 'Assistant Phone'],['Value' => 'BillingInformation', 'Name' => 'Billing Information'],['Value' => 'Birthday', 'Name' => 'Birthday'],['Value' => 'City', 'Name' => 'City'],['Value' => 'City2', 'Name' => 'City2'],['Value' => 'City3', 'Name' => 'City3'],['Value' => 'Company', 'Name' => 'Company'],['Value' => 'CompanyID', 'Name' => 'Company ID'],['Value' => 'ContactNotes', 'Name' => 'Contact Notes'],['Value' => 'ContactType', 'Name' => 'Contact Type'],['Value' => 'Country', 'Name' => 'Country'],['Value' => 'Country2', 'Name' => 'Country2'],['Value' => 'Country3', 'Name' => 'Country3'],['Value' => 'CreatedBy', 'Name' => 'Created By'],['Value' => 'DateCreated', 'Name' => 'Date Created'],['Value' => 'Email', 'Name' => 'Email'],['Value' => 'EmailAddress2', 'Name' => 'Email Address2'],['Value' => 'EmailAddress3', 'Name' => 'Email Address3'],['Value' => 'Fax1', 'Name' => 'Fax1'],['Value' => 'Fax1Type', 'Name' => 'Fax1 Type'],['Value' => 'Fax2', 'Name' => 'Fax2'],['Value' => 'Fax2Type', 'Name' => 'Fax2 Type'],['Value' => 'FirstName', 'Name' => 'First Name'],['Value' => 'Groups', 'Name' => 'Groups'],['Value' => 'Id', 'Name' => 'Id'],['Value' => 'JobTitle', 'Name' => 'Job Title'],['Value' => 'Language', 'Name' => 'Language'],['Value' => 'LastName', 'Name' => 'Last Name'],['Value' => 'LastUpdated', 'Name' => 'Last Updated'],['Value' => 'LastUpdatedBy', 'Name' => 'Last UpdatedBy'],['Value' => 'LeadSourceId', 'Name' => 'Lead SourceId'],['Value' => 'Leadsource', 'Name' => 'Leadsource'],['Value' => 'MiddleName', 'Name' => 'Middle Name'],['Value' => 'Nickname', 'Name' => 'Nickname'],['Value' => 'OwnerID', 'Name' => 'OwnerID'],['Value' => 'Password', 'Name' => 'Password'],['Value' => 'Phone1', 'Name' => 'Phone1'],['Value' => 'Phone1Ext', 'Name' => 'Phone1 Ext'],['Value' => 'Phone1Type', 'Name' => 'Phone1 Type'],['Value' => 'Phone2', 'Name' => 'Phone2'],['Value' => 'Phone2Ext', 'Name' => 'Phone2 Ext'],['Value' => 'Phone2Type', 'Name' => 'Phone2 Type'],['Value' => 'Phone3', 'Name' => 'Phone3'],['Value' => 'Phone3Ext', 'Name' => 'Phone3 Ext'],['Value' => 'Phone3Type', 'Name' => 'Phone3 Type'],['Value' => 'Phone4', 'Name' => 'Phone4'],['Value' => 'Phone4Ext', 'Name' => 'Phone4 Ext'],['Value' => 'Phone4Type', 'Name' => 'Phone4 Type'],['Value' => 'Phone5', 'Name' => 'Phone5'],['Value' => 'Phone5Ext', 'Name' => 'Phone5 Ext'],['Value' => 'Phone5Type', 'Name' => 'Phone5 Type'],['Value' => 'PostalCode', 'Name' => 'Postal Code'],['Value' => 'PostalCode2', 'Name' => 'Postal Code2'],['Value' => 'PostalCode3', 'Name' => 'Postal Code3'],['Value' => 'ReferralCode', 'Name' => 'Referral Code'],['Value' => 'SpouseName', 'Name' => 'Spouse Name'],['Value' => 'State', 'Name' => 'State'],['Value' => 'State2', 'Name' => 'State2'],['Value' => 'State3', 'Name' => 'State3'],['Value' => 'StreetAddress1', 'Name' => 'Street Address1'],['Value' => 'StreetAddress2', 'Name' => 'Street Address2'],['Value' => 'Suffix', 'Name' => 'Suffix'],['Value' => 'TimeZone', 'Name' => 'Time Zone'],['Value' => 'Title', 'Name' => 'Title'],['Value' => 'Username', 'Name' => 'Username'],['Value' => 'Validated', 'Name' => 'Validated'],['Value' => 'Website', 'Name' => 'Website'],['Value' => 'ZipFour1', 'Name' => 'Zip Four1'],['Value' => 'ZipFour2', 'Name' => 'Zip Four2'],['Value' => 'ZipFour3', 'Name' => 'Zip Four3']];
    }

    /**
    * Build an array Infusionsoft Opportunity Field label and corresponding fieldname.
    *
    * @return array of laravel rules.
    */
    protected function getInfusionsoftOpportunityFields()
    {
        return [['Value' => 'ProjectedRevenueHigh', 'Name' => 'Projected Revenue High'],['Value' => 'ProjectedRevenueLow', 'Name' => 'Projected Revenue Low']];
    }

    /**
    * Set infusion client token
    *
    * @return Infusionsoft account record of the current user.
    */
    protected function setTokenClient($id)
    {
        $clientAccount = InfsAccount::where('id', $id)->first();
        if (!$clientAccount) {
            return false;
        }

        $this->infusionsoft->setToken(new InfusionsoftToken([
            'access_token' => $clientAccount->access_token,
            'refresh_token' => $clientAccount->referesh_token,
            'expires_in' => Carbon::parse($clientAccount->expire_date)->timestamp
        ]));

        return $clientAccount;
    }

    /**
    * Save pandadocs tag settings
    *
    * @return \Illuminate\Http\Response redirect
    */
    public function saveTemplateSettingsPandadocs(Request $request)
    {
        $tempID = $request->input('tempID');
        $draft_status_tag = $request->input('draftStatusTag');
        $sent_status_tag = $request->input('sentStatusTag');
        $viewed_status_tag = $request->input('viewedStatusTag');
        $completed_status_tag = $request->input('completedStatusTag');
        $voided_status_tag = $request->input('voidedStatusTag');
        $rejected_status_tag = $request->input('rejectedStatusTag');
        $userID = $this->authUser->id;
        
        if (!empty($tempID)) {
            DocsTagSettings::where('user_id', $userID)->where('template_id', $tempID)->delete();
            if (!empty($draft_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'draft',
                    'applied_tag_id'        => $draft_status_tag
                );
                DocsTagSettings::create($insert);
            }
            
            if (!empty($sent_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'sent',
                    'applied_tag_id'        => $sent_status_tag
                );
                DocsTagSettings::create($insert);
            }
            
            if (!empty($viewed_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'viewed',
                    'applied_tag_id'        => $viewed_status_tag
                );
                DocsTagSettings::create($insert);
            }
            
            if (!empty($completed_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'completed',
                    'applied_tag_id'        => $completed_status_tag
                );
                DocsTagSettings::create($insert);
            }
            
            if (!empty($voided_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'voided',
                    'applied_tag_id'        => $voided_status_tag
                );
                DocsTagSettings::create($insert);
            }
            
            if (!empty($rejected_status_tag)) {
                $insert = array(
                    'user_id'               => $userID,
                    'template_id'               => $tempID,
                    'document_status'       => 'rejected',
                    'applied_tag_id'        => $rejected_status_tag
                );
                DocsTagSettings::create($insert);
            }
        }
        echo "success";
        return true;
    }



    public function listUsersAccount(Request $request)
    {
        $accounts 		= true;
        $panda_accounts = true;

        if (!$this->infusionSoftService->checkISConnection()) {
            $accounts = false;
            // echo $accounts; exit;
        }
        if ($this->listTemplates($request) === "error") {
            $panda_accounts = false;
        }
        return view('userAccounts/list_accounts', compact('accounts', 'panda_accounts'));
    }
    
    public function addUsersAccount()
    {
        $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
            'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ));
        return redirect($this->infusionsoft->getAuthorizationUrl());
    }
    
    public function saveUsersAccount(Request $request)
    {
        $response = array();
        $this->infusionsoft = new Infusionsoft\Infusionsoft(array(
            'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
            'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
            'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
        ));
        //echo "<pre>"; print_r($request->all()); die;
        $code 	  = $request->code;
        if (!$code) {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        $response = $this->infusionsoft->requestAccessToken($code);
        if (!$response) {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }

        $userID 			= $this->authUser->id;
        $startTime 			= date("Y-m-d h:i:s");
        $access_token 		= $response->accessToken;
        $referesh_token 	= $response->refreshToken;
        $expire_after 		= $response->endOfLife;
        $expire_date	  	= date('Y-m-d h:i:s', $expire_after);
        $token_type         = $response->extraInfo['token_type'];
        $scope         		= $response->extraInfo['scope'];
        $scope_arr			= explode("|", $scope);
        $account			= $scope_arr[1];
        if ($this->checkIfAccountExits($userID, $account) == true) {
            $update = array(
                    'access_token'	=>serialize($response),
                    'referesh_token'=>$referesh_token,
                    'expire_date' 	=> $expire_date
                );
            $account = $this->getIsAccount(\Auth::user()->id);
        } else {
            $create = array(
                    'user_id' 		=> $userID,
                    'access_token'	=>serialize($response),
                    'referesh_token'=>$referesh_token,
                    'expire_date' 	=> $expire_date,
                    'account' 		=> $account
                );
            $account = InfsAccount::create($create);
        }
        if ($account) {
            return redirect('/manageaccounts')->with('success', 'Account added successfully');
        } else {
            return redirect('/manageaccounts')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
    }
    
    public function changeStatusOfAccount(Request $request)
    {
        $response  			= '';
        $userID    			= $this->authUser->id;
        $accountID 			= $request->accountID;
        $status    			= $request->status == "active" ? 0: 1;
        $update    			= array(
            'active' 		=> $status,
        );
        $result    			= InfsAccount::where('user_id', $userID)->where('id', $accountID)->update($update);
        if ($result) {
            $accounts 		= InfsAccount::where('user_id', $userID)->get();
            $response 		= view('userAccounts/list_accounts_ajax', compact('accounts'))->render();
        }
        return $response;
    }
    
    public function deleteAccount(Request $request)
    {
        $response  			= '';
        $userID    			= $this->authUser->id;
        $accountID 			= $request->accountID;
        $result   			= InfsAccount::where('user_id', $userID)->where('id', $accountID)->delete();
        if ($result) {
            $accounts 		= InfsAccount::where('user_id', $userID)->get();
            $request->session()->flash('success', 'Account deleted successfully');
            $response 		= view('userAccounts/list_accounts_ajax', compact('accounts'))->render();
        }
        $request->session()->flash('error', 'Error occurs while completing the request. Please try after sometime');
        return $response;
    }
    
    public function checkIfAccountExits($user_id, $account)
    {
        $result				= '';
        $result   			= InfsAccount::where('user_id', $user_id)->where('account', $account)->count();
        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function savePandaCredentials(Request $request)
    {
        $auth_response		= $request->all();
        $code				= @$auth_response['code'];
     
        if (!$code) {
            die('authentication response doesnot have code as output.');
        }
        
        if ($code) {
            $response_array = $this->getAccessToken($code);
            $status  = $this->doSaveAccessToken($response_array);
            
            if ($status) {
                return redirect('/manage-panda-account')->with('success', 'Account added successfully');
            } else {
                return redirect('/manage-panda-account')->with('error', 'Error occurs while completing the request. Please try after sometime');
            }
        }
    }
    
    private function getAccessToken($code)
    {
        $post_flds 			= array(
            'client_id' 	=>'5f761211014489008a92',
            'client_secret'	=>'fcec5ac4abf50f904d295381c74f46cb7330dd3f',
            'scope'			=>'read+write',
            'grant_type' 	=>'authorization_code',
            'code'			=>$code,
            'redirect_uri' 	=> 'http://fuseddocs.com/manage-panda-account/save'
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
            return redirect()->back()->withErrors('Error occurs while completing the request. Please try after sometime.');
        }

        curl_close($ch);
        
        if (!$response) {
            return redirect('/manage-panda-account')->with('error', 'Error occurs while completing the request. Please try after sometime');
        }
        $response_obj_arr = json_decode($response);
        
        if (isset($response_obj_arr->error)) {
            return redirect('/')->with('error', $response_obj_arr->error);
        }
        $response_array   = array(
            'access_token' =>$response_obj_arr->access_token,
            'refresh_token' =>$response_obj_arr->refresh_token,
            'expire_date' =>$response_obj_arr->expires_in,
        );
        return $response_array;
    }
    
    public function doSaveAccessToken($panda_response_array)
    {
        $access_token			= $panda_response_array['access_token'];
        $refresh_token			= $panda_response_array['refresh_token'];
        $expire_date			= $panda_response_array['expire_date'];
        $userID    				= $this->authUser->id;
        $api_key				= $this->randStrGen(20);
        
        if ($this->checkIfPandaAccountExits($userID)) {
            $update 			= array(
                'access_token'	=> $access_token,
                'refresh_token'	=> $refresh_token,
                'expire_date' 	=> $expire_date
            );
            $account = DocsAccountsPanda::where('user_id', $userID)->update($update);
        } else {
            $create 			= array(
                'user_id' 		=> $userID,
                'access_token'	=> $access_token,
                'refresh_token'=> $refresh_token,
                'expire_date' 	=> $expire_date,
                'api_key'		=> $api_key
            );
            $account = DocsAccountsPanda::create($create);
        }
        
        return $account;
    }
    
    
    private function checkIfPandaAccountExits($userID)
    {
        $response		= false;
        $accounts 		= DocsAccountsPanda::where('user_id', $userID)->first();
        
        if (count($accounts)) {
            $response  = true;
        }
        return $response;
    }
    
    
    
    
    
    public function deletePandaUserNotifications(Request $request)
    {
        $notifications		= array();
        $selected_notty_Ids	= $request->selectedNottyArr;
        $userID    			= $this->authUser->id;
        if ($selected_notty_Ids) {
            DocsHttpErrors::where('user_id', $userID)->whereIn('id', $selected_notty_Ids)->delete();
        }
        $response			= DocsHttpErrors::where('user_id', $userID)->orderBy('id', 'desc')->get();
        
        if (count($response)) {
            $notifications	= $response;
        }
        return view('docs.list_notifications_ajax', compact('notifications'));
    }
    
    private function curlRequestToEndPoint($url, $post_flds, $access_token)
    {
        $response 		= "";
        $url 			= $url;
        $headers = array('Authorization: Bearer '.$access_token,'Content-Type: application/json;charset=UTF-8');
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
            return redirect()->back()->withErrors('Some problem occur during Api Call please Try again later.');
        }
        curl_close($ch);
        
        if ($api_response) {
            $response 		= $api_response;
        }

        return $response;
    }
    
    
    
    public function listTemplates(Request $request)
    {
        $userID 		= $this->authUser->id;
        $account 		= DocsAccountsPanda::where('user_id', $userID)->first();
        $templates		= array();
        
        // if ( !count($account) && empty($account) ) {
        if (empty($account)) {
            return "error";
        }
        
        $access_token 	= $account->access_token;
        $url			= "https://api.pandadoc.com/public/v1/templates";
        $post_flds  	= array();
        try {
            $api_response = $this->curlRequestToEndPoint($url, $post_flds, $access_token);
        } catch (\Exception $exception) {
            return "error";
        }
        if (!$api_response) {
            return "error";
        }
        $api_response_array			= json_decode($api_response);
        
        if (!isset($api_response_array->results)) {
            return "error";
        }
        
        if (count($api_response_array->results) && !empty($api_response_array->results)) {
            foreach ($api_response_array->results as $temp) {
                $templates[$temp->id] = $temp->name;
            }
            return $templates;
        } else {
            return "error";
            exit;
        }
    }
    
    

    public function SyncNotificationsCount()
    {
        $notty_count		= 0;
        $userID 			= $this->authUser->id;
        $response			= DocsHttpErrors::where('user_id', $userID)->where('status', 0)->get();
        
        if (count($response)) {
            $notty_count = count($response);
        }
        return $notty_count;
    }
    
    

    
    private function getContactIDOFRecipientIFExist($infusionsoft, $recipient_email)
    {
        $contact_is_id          = "";
        $query_fields           = array('Email' => $recipient_email);
        $returnFields           = array('Id');
        $is_result              = $infusionsoft->data()->query("Contact", 10, 0, $query_fields, $returnFields, 'Id', true);
        
        if (is_array($is_result) && count($is_result) > 0) {
            $contact_is_id = $is_result[0]['Id'];
        }
        
        return $contact_is_id;
    }

    private function getContactDataByIdFromInfusionSoft($infusionsoft, $id)
    {
        $contact_is_id          = "";
        $query_fields           = array('Id' => (int)$id);
        $returnFields           = array('Id', 'Email');
        $is_result              = $infusionsoft->data()->query("Contact", 10, 0, $query_fields, $returnFields, 'Id', true);
        if (is_array($is_result) && count($is_result) > 0) {
            $contact_is_id = $is_result[0]['Id'];
        }

        return $contact_is_id;
    }
    
    private function applyTagToContact($infusionsoft, $contact_id, $tagID)
    {
        $contactId  = $contact_id;
        $tagId      = $tagID;
        
        $result     = $infusionsoft->contacts('xml')->addToGroup($contactId, $tagId);
        return $result;
    }
    
    private function getTags($IS_account_id)
    {
        $tags                       = "";

        if ($IS_account_id) {
            // get all tags from infusionsoft account

            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));

            $client_is_account      = InfsAccount::where('id', $IS_account_id)->first();
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
            $query_fields         = array('GroupName' =>'%');
            $returnFields         = array('Id','GroupName');
            $tags_arr             = $infusionsoft->data()->query("ContactGroup", 1000, 0, $query_fields, $returnFields, 'Id', true);

            $final_arr            = array();

            if (count($tags_arr)) {
                foreach ($tags_arr as $arr) {
                    $data['id'] = $arr['Id'];
                    $data['GroupName'] = isset($arr['GroupName']) ? $arr['GroupName']: '';
                    if (!empty($data['GroupName'])) {
                        $final_arr[$data['id']] = $data['GroupName'];
                    }
                }
            }
            $tags     = $final_arr;
        }

        return json_encode($tags);
    }
    
    public function getOpportunityByProducts($contactID=7613)
    {
        $IS_account_id = $this->authUser->id;
        
        $infusionsoft = new Infusionsoft\Infusionsoft(array(
            'clientId'          => config('infusionsoft.clientId'),
            'clientSecret'      => config('infusionsoft.clientSecret'),
            'redirectUri'       => config('infusionsoft.redirectUri'),
        ));

        $client_is_account      = InfsAccount::where('user_id', $IS_account_id)->first();
        $access_token           = $client_is_account->access_token;
        $infusionsoft->setToken(unserialize($access_token));

        if ($infusionsoft->getToken()) {
            try {
                //nothing
            } catch (\Infusionsoft\TokenExpiredException $e) {
                $infusionsoft->refreshAccessToken();
            }
        }

        $query_fields = array('ContactID' => $contactID);
        $returnFields = array('Id','ContactID','StageID','StatusID','UserID','OpportunityTitle');

        
        $opportunity =  $infusionsoft->data()->query('Lead', 1000, 0, $query_fields, $returnFields, 'Id', false);
        
        $prod_res = [];
        
        if (count($opportunity) == 0) {
            return $prod_res;
        }
    
        $query_fields = array('ObjectId' => $opportunity[0]['Id']);
        $returnFields = array('ObjType','ProductId','ProductType','Qty','SubscriptionPlanId');
    
        $product_int =  $infusionsoft->data()->query('ProductInterest', 1000, 0, $query_fields, $returnFields, 'Id', true);
        
        $i = 0;
        foreach ($product_int as $int) {
            if ($int["ProductId"] != 0) {
                $prod_res[$i]["Qty"] = $int["Qty"];
                
                $query_fields = array('Id' => $int["ProductId"]);
                $returnFields = array('ProductName','ProductPrice');
                $product =  $infusionsoft->data()->query('Product', 1000, 0, $query_fields, $returnFields, 'Id', true);
                if (is_array($product) && count($product)>0) {
                    $prod_res[$i]["ProductName"] = $product[0]["ProductName"];
                    $prod_res[$i]["ProductPrice"] = $product[0]["ProductPrice"];
                }
            }
            $i++;
        }
        echo "<pre>";
        print_r($prod_res);
        echo "</pre>";
    }
    
    
    
    public function getIsAccount($user)
    {
        return \CommanHelper::infsTokenByUserId($user);
    }
}
