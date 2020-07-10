<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\InfsAccount;
use App\DocsCompleted;
use App\UserUsageDocs;
use App\DocsAccountsDocsign;
use App\DocumentCreateLog;
use App\DocsHttpErrors;
use App\Services\DocusignService;
use App\Services\InfusionSoftService;
use App\Http\Requests;

class DocusignController extends Controller
{
    private $FuseKey		        ="";
    private $contact_id				="";
    private $user					="";
    private $temp_id				="";
    private $roles                  ="";
    private $app                    ="";
    private $mails                  =array();
    
    protected $infusionSoftService;
    protected $docusignService ;

    public function __construct(InfusionSoftService $infusionSoftService, DocusignService $docusignService)
    {
        $this->infusionSoftService = $infusionSoftService;
        $this->docusignService = $docusignService;
    }

    /**
    * Receives the INFS http post and process it immediately, Fire a background process to actually process the
    * docusign docs creation.
    *
    * @return \Illuminate\Http\Response
    */
    public function createDocument(Request $request)
    {
        $data = $request->all();
        $documentCreateLogRecord = DocumentCreateLog::create(['data' => json_encode($data), 'app' => 'docusign']);

        $processUri = url('/docusign/process/' . $documentCreateLogRecord->id);
        exec('wget "'.$processUri.'" --delete-after >/dev/null 2>&1 &');
    }

    public function processCreateDocument($id)
    {
        $documentCreateLogRecord = DocumentCreateLog::find($id);
        if (!$documentCreateLogRecord) {
            $this->sendNotification("no_log_record", "No such record " . $id, 'Failed');
            die('No such record.');
        }

        $params = json_decode($documentCreateLogRecord->data, true);

 
        $this->FuseKey 	     = isset($params['FuseKey']) ? $params['FuseKey'] : "";
        $this->app 	         = isset($params['app']) ? $params['app'] : "";
        $this->contact_id 	 = isset($params['contactId']) ? $params['contactId'] : "";
        $this->temp_id		 = isset($params['TemplateID']) ? $params['TemplateID'] : "";
        
        if (empty($this->FuseKey)) {
            die('Missing Mandatory Field FuseKey.');
        }
        
        $this->user = User::where('FuseKey', $this->FuseKey)->first();
        
        if (empty($this->user)) {
            die('User not found.');
        }
        if (empty($this->app)) {
            $this->sendNotification("docusign_missing_mandatory_field", "Missing Mandatory Field app", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Mandatory Field app.");
            $this->sendEmail($this->user->email, "FusedTools-DocuSign Notifications", $message);
            die('Missing Mandatory Field app.');
        }
        if (empty($this->contact_id)) {
            $this->sendNotification("docusign_missing_mandatory_field", "Missing Mandatory Field contact_id", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Mandatory Field contact_id.");
            $this->sendEmail($this->user->email, "FusedTools-DocuSign Notifications", $message);
            die('Missing Mandatory Field contact_id.');
        }
        if (empty($this->temp_id)) {
            $this->sendNotification("docusign_missing_mandatory_field", "Missing Mandatory Field temp_id", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Mandatory Field temp_id.");
            $this->sendEmail($this->user->email, "FusedTools-DocuSign Notifications", $message);
            die('Missing Mandatory Field temp_id.');
        }
        
        //Get infs account
        $infsAcc = InfsAccount::where('user_id', $this->user->id)->where('account', $this->app.'.infusionsoft.com')->first();
        if (empty($infsAcc)) {
            $this->sendNotification("docusign_missing_mandatory_field", "Infs account not found", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Infs account not found.");
            $this->sendEmail($this->mails, "FusedTools-DocuSign Notifications", $message);
            die('Infs account not found.');
        } else {
            $this->app = $infsAcc;
        }
        
        //Get docusign account
        $docuAccount = DocsAccountsDocsign::where('user_id', $this->user->id)->first();
        if (!$docuAccount) {
            $this->sendNotification("docusign_account", "Missing Docusign Account", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Missing Docusign Account.");
            $this->sendEmail($this->user->email, "FusedTools-DocuSign Notifications", $message);
            die('Missing Docusign Account.');
        }
        if ($docuAccount && $docuAccount->expires_date < Carbon::now()) {
            $this->sendNotification("docusign_account", "Your Docusign Token Expired.", 'Failed', [], $documentCreateLogRecord);
            $message 				= $this->getMessage("Your Docusign Token Expired.");
            $this->sendEmail($this->user->email, "FusedTools-DocuSign Notifications", $message);
            die('Your Docusign Token Expired.');
        }
        
        $docLimit = $this->user->documentLimit;
        if ($docLimit === false) {
            // Applied tag when user is overlimit.
            $docName = $infsParams['doc_name'] ?? 'Untitled Doc';
            $this->sendNotification("over_limit", " $docName failed", 'Failed', [], $documentCreateLogRecord);
            //$this->infusionSoftService->applyTagUsigTagId($this->user->infusionsoft_contact_id,188);
            die("Document sent failed - docs limit reached");
        }

        //Get template details
        $templateDetails = $this->docusignService->getTemplateDetails($params['TemplateID'], $docuAccount->access_token);
        $result = $this->docusignService->createDocumentFromTemplate($docuAccount->access_token, $params['TemplateID'], $templateDetails, $params);

        if (!isset($result['result']) || $result['result'] === false) {
            $msg = $result['msg'] ?? "Failed to create document";
            $this->sendNotification("docusign_account", $msg, 'Failed', [], $documentCreateLogRecord);
        } else {
            UserUsageDocs::updateOrCreate(['user_id' => $this->user->id], ['docs_sent' => $this->user->userUsageCount+1 ]);
            $documentCreateLogRecord->status = 1;
            $documentCreateLogRecord->save();

            $fieldsLog = (array) $params;
            $fieldLogs = array_keys($fieldsLog);
            $docData = [
            'user_id'  =>  $this->user->id,
            'infs_account_id'  =>  $this->app->id,
            'document_id'  =>  $result['msg']['envelope_id'],
            'template_id'  =>  $this->temp_id,
            'http_post_log'  =>  json_encode($fieldLogs),
            'contactId'  => $this->contact_id,
            'type'  => 'docusign' ];
            DocsCompleted::create($docData);

            \Mail::send('emails.tokenRenew', ['response' => $params], function ($message) {
                $message->from('help@fusedtools.com', 'FusedTools');
                $message->to('ted@fusedsoftware.com')->subject('INFS Docusign response');
            });
        }


        //Update template
        //$templateBody = '';
        //$this->docusignService->updateTemplate($docuAccount->access_token,$this->temp_id,$templateBody);
    }
    
    private function sendNotification($error_type, $message, $status, $document_data = '', $documentCreateLogRecord = false)
    {
        $insert = array(
            'user_id'		=> $this->user->id,
            'error_type' 	=> $error_type,
            'message'		=> $message,
            'template_id'	=> $this->temp_id,
            'contactId'     => $this->contact_id,
            'status'        => $status
        );
        
        if (isset($this->app->id)) {
            $insert['infs_account_id'] = $this->app->id;
        }
        DocsHttpErrors::create($insert);
        if ($documentCreateLogRecord) {
            $documentCreateLogRecord->logError($message);
        }
    }
    
    private function getMessage($message)
    {
        $content  = "<h1>Hello ".$this->user->first_name."</h1>";
        $content .= "<p>An error has occurred while generating and sending your DocuSign.</p>";
        $content .= "<p>Error Message: ".$message."</p><br>";
        return $content;
    }
}
