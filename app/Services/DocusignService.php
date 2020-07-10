<?php
namespace App\Services;

use App\DocsAccountsDocsign;
use Illuminate\Http\Request;
use DocuSign;

class DocusignService
{
    private $authUser 	 = '';
    
    public function __construct()
    {
        $this->authUser = \Auth::user();
    }

    public function auth($userToken = '')
    {
        $username = env('DOCUSIGN_USERNAME');
        $password = env('DOCUSIGN_PASSWORD');
        $integrator_key = env('DOCUSIGN_KEY');

        // change to production (www.docusign.net) before going live
        $host = env('DOCUSIGN_URL');

        // create configuration object and configure custom auth header
        $config = new DocuSign\eSign\Configuration();
        $config->setHost($host);
        if ($userToken == '') {
            $token = $this->authUser->DocuSignToken;
        } else {
            $token = $userToken;
        }
        $config->addDefaultHeader("Authorization", "Bearer ".$token);
        
        // instantiate a new docusign api client
        $apiClient = new \DocuSign\eSign\ApiClient($config);
        $accountId = null;
        $data = array();
        
        try {
            $authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);
            $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
            $loginInformation = $authenticationApi->login($options);
            if (isset($loginInformation) && count($loginInformation) > 0) {
                $loginAccount = $loginInformation->getLoginAccounts()[0];
                $host = $loginAccount->getBaseUrl();
                $host = explode("/v2", $host);
                $host = $host[0];
    
                // UPDATE configuration object
                $config->setHost($host);
        
                // instantiate a NEW docusign api client (that has the correct baseUrl/host)
                $data['apiClient'] = new DocuSign\eSign\ApiClient($config);
                
                if (isset($loginInformation)) {
                    $accountId = $loginAccount->getAccountId();
                    if (!empty($accountId)) {
                        $data['accountId'] = $accountId;
                    }
                }
            }
        } catch (DocuSign\eSign\ApiException $ex) {
            $data['error'] = $ex->getMessage();
        }
        return $data;
    }
    
    public function getTemplates($account)
    {
        $auth = $this->auth();
        $templates = '';
        if (isset($auth['apiClient']) && $auth['accountId']) {
            $templateApi = new DocuSign\eSign\Api\TemplatesApi($auth['apiClient']);
            $templates = $templateApi->listTemplates($auth['accountId']);
        }
        return $templates;
    }
    
    public function getTemplateDetails($templateId, $token = '')
    {
        $auth = $this->auth($token);
        $templateDetails = '';
        if (isset($auth['apiClient']) && $auth['accountId']) {
            $templateApi = new DocuSign\eSign\Api\TemplatesApi($auth['apiClient']);
            $templateDetails = $templateApi->get($auth['accountId'], $templateId);
        }
        /* echo "<pre>";
         var_dump($templateDetails['recipients']['signers'][0]['tabs']);exit;*/
        return $templateDetails;
    }
    
    public function updateTemplate($token, $templateId, $templateBody)
    {
        $auth = $this->auth($token);
        $templateDetails = '';
        if (isset($auth['apiClient']) && $auth['accountId']) {
            $templateApi = new DocuSign\eSign\Api\TemplatesApi($auth['apiClient']);
            $templateDetails = $templateApi->update($auth['accountId'], $templateId, $templateBody);
        }
        return $templateDetails;
    }

    public function createDocumentFromTemplate($userToken = "", $templateId, $templateDetails, $infsParams)
    {
        $username = env('DOCUSIGN_USERNAME');
        $password = env('DOCUSIGN_PASSWORD');
        $integrator_key = env('DOCUSIGN_KEY');

        // change to production (www.docusign.net) before going live
        $host = env('DOCUSIGN_URL');

        // create configuration object and configure custom auth header
        $config = new DocuSign\eSign\Configuration();
        $config->setHost($host);
        if ($userToken == '') {
            $token = $this->authUser->DocuSignToken;
        } else {
            $token = $userToken;
        }
        $config->addDefaultHeader("Authorization", "Bearer ".$token);
        
        // instantiate a new docusign api client
        $apiClient = new \DocuSign\eSign\ApiClient($config);
        $accountId = null;
        $data = array();
        
        try {
            $authenticationApi = new DocuSign\eSign\Api\AuthenticationApi($apiClient);
            $options = new \DocuSign\eSign\Api\AuthenticationApi\LoginOptions();
            $loginInformation = $authenticationApi->login($options);
            if (isset($loginInformation) && count($loginInformation) > 0) {
                $loginAccount = $loginInformation->getLoginAccounts()[0];
                $host = $loginAccount->getBaseUrl();
                $host = explode("/v2", $host);
                $host = $host[0];
    
                // UPDATE configuration object
                $config->setHost($host);
        
                // instantiate a NEW docusign api client (that has the correct baseUrl/host)
                $data['apiClient'] = new DocuSign\eSign\ApiClient($config);
                
                if (isset($loginInformation)) {
                    $accountId = $loginAccount->getAccountId();
                    if (!empty($accountId)) {
                        //*** STEP 2 - Signature Request from a Template
                        // create envelope call is available in the EnvelopesApi
                        $envelopeApi = new DocuSign\eSign\Api\EnvelopesApi($apiClient);
                        //$envelopeApi->setCustomField
                        // assign recipient to template role by setting name, email, and role name.  Note that the
                        // template role name must match the placeholder role name saved in your account template.



                        //get template details
                        //for each roles
                        // loop through textfields
                        // get tab label name and match with the request
                        // if matched, setTabLabel setValue

                        $allTabs = config('docuSign.fields');
                        $tabClass = DocuSign\eSign\Model\Tabs::swaggerTypes();
                        $tabSetterMethod = DocuSign\eSign\Model\Tabs::setters();
                        $tabObj = new DocuSign\eSign\Model\Tabs();
                        $docName = $infsParams['doc_name'] ?? 'Untitled Doc';
                        //echo "<pre>";
                        /*var_dump($tabClass);;
                        var_dump($tabSetterMethod);exit;*/
                        $templateRoles = [];
                        $tabList = [];
                        foreach ($templateDetails['recipients']['signers'] as $recipient) {
                            //var_dump($recipient);
                            $roleName = $recipient["role_name"];
                            $roleEmail = $recipient["email"];
                            $roleFullName = $recipient["name"];
                            
                            $templateRole = new  DocuSign\eSign\Model\TemplateRole();
                            $templateRole->setEmail($roleEmail);
                            $templateRole->setName($roleFullName);
                            $templateRole->setRoleName($roleName);
                            
                            foreach ($allTabs as $tab) {
                                if (count($recipient['tabs'][$tab]) > 0) {
                                    foreach ($recipient['tabs'][$tab] as $name) {
                                        if (strpos(strtolower($name['tab_label']), "signature") === false) {
                                            if (!isset($tabList[$tab])) {
                                                $tabList[$tab] = [];
                                            }
                                            $tabValue = $infsParams[$name['tab_label']] ?? '';
                                            $className = str_replace("[]", '', $tabClass[$tab]);
                                            $dynamicTabObj = new $className;
                                            $dynamicTabObj->setTabLabel("\\*".$name['tab_label']);
                                            $dynamicTabObj->setValue($tabValue);

                                            $tabList[$tab][] = $dynamicTabObj;
                                        }
                                    }
                                }
                            }

                            foreach ($tabSetterMethod as $key => $method) {
                                if (isset($tabList[$key])) {
                                    $tabObj->$method($tabList[$key]);
                                }
                            }

                            $templateRole->setTabs($tabObj);

                            $templateRoles[] = $templateRole;
                        }
                        // instantiate a new envelope object and configure settings
                        $eventNotification = $this->setUpEventNotification();

                        $envelopDefinition = new DocuSign\eSign\Model\EnvelopeDefinition();
                        $envelopDefinition->setEmailSubject($docName);
                        $envelopDefinition->setTemplateId($templateId);
                        $envelopDefinition->setTemplateRoles($templateRoles);
                        $envelopDefinition->setEventNotification($eventNotification);
                        
                        // set envelope status to "sent" to immediately send the signature request

                        if ($infsParams['Status'] == 1) {
                            $envelopDefinition->setStatus("sent");
                        } elseif ($infsParams['Status'] == 0) {
                            $envelopDefinition->setStatus("created");
                        } else {
                            return ['result'=>false,'msg' => 'invalid status code'];
                        }

                        // optional envelope parameters
                        $options = new \DocuSign\eSign\Api\EnvelopesApi\CreateEnvelopeOptions();
                        $options->setCdseMode(null);
                        $options->setMergeRolesOnDraft(null);

                        // create and send the envelope (aka signature request)
                        $envelopSummary = $envelopeApi->createEnvelope($accountId, $envelopDefinition, $options);

                        if (!empty($envelopSummary)) {
                            return  ['result'=>true,'msg' => $envelopSummary];
                        }
                        return ['result'=>false,'msg' => 'failed to create document'];
                    }
                }
            } else {
                return ['result'=>false,'msg' => 'no login information'];
            }
        } catch (DocuSign\eSign\ApiException $ex) {
            $data['error'] = $ex->getMessage();
            return ['result'=>false,'msg' => $data['error']];
        }
    }


    private function setUpEventNotification()
    {
        $envelopeEvents  = [
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("delivered"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("completed"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("declined"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("voided"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent"),
            (new \DocuSign\eSign\Model\EnvelopeEvent())->setEnvelopeEventStatusCode("sent")
        ];

        $eventNotification = new \DocuSign\eSign\Model\EventNotification();
        $eventNotification->setUrl(env('DOCUSIGN_WEBHOOK_URL'));
        $eventNotification->setLoggingEnabled("false");
        $eventNotification->setRequireAcknowledgment("true");
        $eventNotification->setUseSoapInterface("false");
        $eventNotification->setIncludeCertificateWithSoap("false");
        $eventNotification->setSignMessageWithX509Cert("false");
        $eventNotification->setIncludeDocuments("false");
        $eventNotification->setIncludeEnvelopeVoidReason("true");
        $eventNotification->setIncludeTimeZone("true");
        $eventNotification->setIncludeSenderAccountAsCustomField("true");
        $eventNotification->setIncludeDocumentFields("false");
        $eventNotification->setIncludeCertificateOfCompletion("true");
        $eventNotification->setEnvelopeEvents($envelopeEvents);

        return $eventNotification;
    }
}
