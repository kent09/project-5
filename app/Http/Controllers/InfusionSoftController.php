<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\XeroService;
use App\Services\InfusionSoftService;
use App\Http\Requests;
use Infusionsoft;
use Carbon\Carbon;
use Excel;
use App\InfsAccount;
use App\PostcOwner;
use App\PostcOwnerGroups;
use App\PostcCountries;
use App\PostcTags;
use App\XeroAccounts;
use App\xeroInvoiceSetting;
use App\XeroCronSync;
use App\XeroCronSyncResult;
use Illuminate\Support\Facades\Schema;
use Auth;

class InfusionSoftController extends Controller
{
    public $status_ar = [0=>'Deleted', 1=>'Complete', 2=>'Pending', 3=>'In Progress'];

    private $authUser = array();
    protected $infusionSoftService;
    
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        // $this->authUser = Auth::user();
        $this->infusionSoftService = $infusionSoftService;
    }
    
    public function index()
    {
    }

    public function getInfusionsoftAccount(Request $request)
    {
        $params = $request->all();
        
        $accountId = $params['accountID'];
        $userId = Auth::id();
        $infsAccount = InfsAccount::where('user_id', $userId)->where('id', $accountId)->first();
        if (empty($infsAccount)) {
            return array( 'status' => 'failed', 'message' => 'Account not found.' );
        }

        return array( 'status' => 'success', 'app_name' => $infsAccount->name );
    }
    
    public function getAllStage(Request $request)
    {
        $params = $request->all();
        
        $accountId = $params['accountID'];
        $userId = Auth::id();
        $infsAccount = InfsAccount::where('user_id', $userId)->where('id', $accountId)->first();
        if (empty($infsAccount)) {
            return array( 'status' => 'failed', 'message' => 'Account not found.' );
        }
        $stages = $this->infusionSoftService->getAllStage($userId, $accountId);
        $response = view('scripts.partial.stageIds', compact('stages'))->render();
        
        return array( 'status' => 'success', 'message' => $response, 'app_name' => $infsAccount->name );
    }
    
    public function getAllMerchants(Request $request)
    {
        $params = $request->all();
        
        $accountId = $params['accountID'];
        $userId = Auth::id();
        $infsAccount = InfsAccount::where('user_id', $userId)->where('id', $accountId)->first();
        if (empty($infsAccount)) {
            return array( 'status' => 'failed', 'message' => 'Account not found.' );
        }
        $merchants = $this->infusionSoftService->getAllMerchants($userId, $accountId);
        $response = view('scripts.partial.merchantIds', compact('merchants'))->render();
        
        return array( 'status' => 'success', 'message' => $response, 'app_name' => $infsAccount->name );
    }
    
    public function calculateDate(Request $request)
    {
        $params = $request->all();
        $response = array();
        
        $startdate = $params['startDate'];
        $addTime = $params['addTime'];
        
        $time = date('d-m-Y h:m', strtotime($addTime, strtotime($startdate)));
        
        // if( $startdate == 'today' || $startdate == 'Today' ){
        //     $startdate = Carbon::now();
        // }
        // elseif( $startdate == 'tomorrow' || $startdate == 'Tomorrow' ){
        //     $startdate = Carbon::now()->tomorrow();
        // }
        // elseif( $startdate == 'yesterday ' || $startdate == 'Yesterday ' ){
        //     $startdate = Carbon::now()->yesterday();
        // }
        // else{
        //     $startdate = Carbon::createFromFormat('d-m-Y',$startdate);
        // }
        
        // if( isset($addTime[2]) ){
        //     if( $addTime[2] == 'hours' ){
        //         $time = $startdate->addHours($addTime[0].$addTime[1])->format('d-m-Y');
        //     }
        //     elseif( $addTime[2] == 'day' ){
        //         $time = $startdate->addDays($addTime[0].$addTime[1])->format('d-m-Y');
        //     }
        //     elseif( $addTime[2] == 'week' ){
        //         $time = $startdate->addWeeks($addTime[0].$addTime[1])->format('d-m-Y');
        //     }
        // }
        
        $response['time'] = $time;
        return $response;
    }
    
    public function postCtags(Request $request)
    {
        $params = $request->all();
        $response = array();
        
        $postTags = PostcTags::where('user_id', Auth::id())->where('infs_account_id', $params['accountID'])->where('status', '<>', 0)->get();

        if (count($postTags) == 0) {
            return array( 'status' => 'failed', 'message' => 'Tags not found.' );
        }
        $status_ar = $this->status_ar;
        $response = view('scripts.partial.postTags', compact('postTags', 'status_ar'))->render();

        return array( 'status' => 'success', 'message' => $response );
    }
    
    public function postCode(Request $request)
    {
        $params = $request->all();
        $response = array();

        if(!Schema::hasTable('postc_codes_'.strtolower($params['country']))) {
            return array( 'status' => 'failed', 'message' => 'Postcode not found.' );
        }    
        $suburb = \DB::table('postc_codes_'.strtolower($params['country']))->where('postcode', $params['code'])->first();

        if (!$suburb) {
            return array( 'status' => 'failed', 'message' => 'Postcode not found.' );
        }

        $suburbContent = '('.$suburb->region1.', '.$suburb->suburb.' '.$suburb->postcode.')';

        return array( 'status' => 'success', 'suburb' => $suburbContent );
    }

    public function tagContact(Request $request)
    {
        $params = $request->all();
        $response = array();
        
        $suburb = \DB::table('postc_codes_'.strtolower($params['country']))->where('postcode', $params['postcode'])->first();
       
        // $tagname =  $suburb->suburb.' + '.$params['radius'].$params['unit'];
        // $tagid = $this->infusionSoftService->createTags($this->authUser->id, $params['account'], $tagname);
        
        $data['user_id'] = Auth::id();
        $data['infs_account_id'] = $params['account'];
        $data['postc_country_code'] = $params['country'];
        // $data['tag_id'] = $tagid;
        $data['status'] = 2;
        
        if ($params['areagroup'] == 'radius_around_postcode') {
            $data['postc_code'] = $params['postcode'];
            $data['postc_radius'] = $params['radius'];
            $data['postc_units'] = $params['unit'];
            $data['postc_type'] = 1;
        } else {
            $data['postc_type'] = 2;
            $data['postc_list'] = $params['areagrouptext'];
        }
    
        $Post_table_status = PostcTags::create($data);

        $status_ar = $this->status_ar;
        
        $postTags = PostcTags::where('user_id', Auth::id())->where('infs_account_id', $params['account'])->where('status', '<>', 0)->get();

        if (count($postTags) == 0) {
            return array( 'status' => 'failed', 'message' => 'Tags not found.' );
        }

        $tags = view('scripts.partial.postTags', compact('postTags', 'status_ar'))->render();
        
        return array( 'status' => 'success', 'message' => 'Tag Created & Contacts Tagged.' , 'response' => $tags );
    }
    
    public function applyTagOnCron()
    {

        #fetch data from PostcTags
        $postcode_data = PostcTags::where('status', 2)
                            ->whereNotExists(
                                function ($q) {
                                    $q->select('id')->from('postc_tags as p')->where('p.status', 3)->whereRaw('p.user_id = postc_tags.user_id')->whereRaw('p.infs_account_id = postc_tags.infs_account_id');
                                }
                            )
                            ->first();
        if (count((array) $postcode_data) == 0) {
            return;
        }

        \Log::info('Post Code Data: ' . print_r($postcode_data, true));
        \Log::info('Post Tagging Executed...');

        #mark this to status=3
        PostcTags::where("id", $postcode_data->id)->update(['status'=>3]);

        $postc_tag_status = PostcTags::where("id", $postcode_data->id)->first();
        \Log::info('PostcTag new Status is: ' . print_r($postc_tag_status->status, true));

        if ($postcode_data->postc_type == 1 || $postcode_data->postc_type == 0) {
            
        // 	$suburb = \DB::table('postc_codes_'.$postcode_data->postc_country_code)->where('postcode',$postcode_data->postc_code)->first();
    
            $tagname =  $postcode_data->postc_code.' + '.$postcode_data->postc_radius.$postcode_data->postc_units;
        } elseif ($postcode_data->postc_type == 2) {
            $tagname =  Carbon::parse($postcode_data->created_at)->format('d-m-Y H:i').' List';
        }

        #call the PostcodeTaggingService->tagBasedOnRadius
        $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
        $obj->deleteAreaRmvTag($postcode_data->id, 1, $postcode_data->user_id);
        $tagCount = $obj->tagBasedOnRadius($postcode_data->id, $tagname, $postcode_data->user_id);

        PostcTags::where('id', $postcode_data->id)->update([ 'tag_count' => $tagCount, 'status' => 1 ]);

        $user_data = \App\User::where("id", $postcode_data->user_id)->first();
        $email = $user_data->email;

        $postcode_data = PostcTags::where('id', $postcode_data->id)->first();

        $content = "The contacts inside your specific infusionsoft account have now been tagged based on the following criteria:<br><br>";
        
        if ($postcode_data->postc_type == 1 || $postcode_data->postc_type == 0) {
            
            // $content .= "<b>Suburb Name: </b>".$suburb->suburb."<br>";
            $content .= "<b>Postcode: </b>".$postcode_data->postc_code."<br>";
            $content .= "<b>Radius: </b>".$postcode_data->postc_radius." ".$postcode_data->postc_units."<br>";
        } elseif ($postcode_data->postc_type == 2) {
            $content .= "<b>Postcode List: </b>".$postcode_data->postc_list."<br>";
        }
        $content .= "<b>The tag was: </b> ".$tagname." (ID: ".$postcode_data->tag_id.")<br>";

        #send an email
        # catch if there is an exception and will do the re-attempt for 3x
        $this->infusionSoftService->handler(function () use ($content, $email, $user_data) {
            \Mail::send('emails.tagEmail', [ 'content' => $content ], function ($message) use ($email,$user_data) {
                $message->from('help@fusedtools.com', 'FusedTools');
                $message->to(\CommanHelper::notifyEmails($user_data->id))->bcc("help@fusedtools.com")->subject('Postcode Tagging Complete');
                // $message->to('jerson.s.ramos@gmail.com')->bcc("jerson@fusedsoftware.com")->subject('Postcode Tagging Complete');
            });
        });
    }

    public function reApplyTag(Request $request)
    {
        $params = $request->all();
        $response = array();
        if (isset($params['id']) && $params['id'] > 0) {
            $postcode_data = PostcTags::where('id', $params['id'])->first();
            PostcTags::where('user_id', Auth::id())->where('id', $params['id'])->where('status', 1)->update(["status"=>2]);
            $postTags = PostcTags::where('user_id', Auth::id())->where('infs_account_id', $postcode_data->infs_account_id)->where('status', '<>', 0)->get();
            $status_ar = $this->status_ar;
            $tags = view('scripts.partial.postTags', compact('postTags', 'status_ar'))->render();

            return array( 'status' => 'success', 'message' => 'Your Tag Reassignment Has Been Queued. You will receive an email once it is complete.' , 'response' => $tags );
        }
        
        return array( 'status' => 'fail', 'message' => 'Something went wrong.' , 'response' => "" );
    }

    public function deletePostcodeTag(Request $request)
    {
        $user_id = Auth::id();
        $params = $request->all();
        $response = array();
        if (isset($params['id']) && $params['id'] > 0) {
            $postcode_data = PostcTags::where('user_id', $user_id)->where('id', $params['id'])->where('status', 1)->first();

            \Log::info('ID: '.print_r($params['id'], true));
            \Log::info('User: '.print_r($user_id, true));
            \Log::info('Postcode_data: '.print_r($postcode_data, true));

            if (!$postcode_data) {
                return array( 'status' => 'fail', 'message' => 'Something went wrong.' , 'response' => '');
            }

            #call the PostcodeTaggingService->tagBasedOnRadius
            $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
            $obj->deleteAreaRmvTag($postcode_data->id, 0);

            $postTags = PostcTags::where('user_id', $user_id)->where('infs_account_id', $postcode_data->infs_account_id)->where('status', '<>', 0)->get();
            $status_ar = $this->status_ar;

            $tags = view('scripts.partial.postTags', compact('postTags', 'status_ar'))->render();
            
            return array( 'status' => 'success', 'message' => 'Tag Deleted.' , 'response' => $tags );
        }
        
        return array( 'status' => 'fail', 'message' => 'Something went wrong.' , 'response' => "" );
    }
    
    public function radiusMap(Request $request)
    {
        $params = $request->all();
        $response = array();

        if(!Schema::hasTable('postc_codes_'.strtolower($params['country']))) {
            return array( 'status' => 'failed', 'message' => 'Postcode not found.' );
        }

        $latLong = $suburb = \DB::table('postc_codes_'.strtolower($params['country']))->where('postcode', $params['postcode'])->first();
        
        if (!$latLong) {
            return array( 'status' => 'failed', 'message' => 'Postcode not found.' );
        }
        
        $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
        $response['list'] = $obj->getPostcList($latLong->latitude, $latLong->longitude, $params['country'], $params['postcode'], $params['radius'], $params['unit']);
        
        $response['lat'] = $latLong->latitude;
        $response['long'] = $latLong->longitude;
        return array( 'status' => 'success', 'response' => $response );
    }
    
    public function postOwner(Request $request)
    {
        $params = $request->all();
        $response = array();
        
        $infs_account_id = $params['accountID'];
        
        $postOwner = PostcOwner::with('group')->where('infs_account_id', $infs_account_id)->where('user_id', Auth::id())->where('status', 1)->get();

        $infs = InfsAccount::find($infs_account_id);

        $app_name = $infs->count() > 0 ? $infs['name'] : null;
        
        $owners = view('scripts.partial.postOwner', compact('postOwner', 'infs_account_id'))->render();
        
        return array( 'status' => 'success', 'response' => $owners, 'app_name' => $app_name );
    }
    
    public function addOwner(Request $request)
    {
        $params = $request->all();
        $contacts = $this->infusionSoftService->getUsers( Auth::id(), $params['accountID']);
       
        $countries = PostcCountries::where('country_code', '<>', '')->get();
        
        $owners = view('scripts.partial.addOwner', compact('contacts', 'countries'))->render();
        return array( 'status' => 'success', 'response' => $owners );
    }
    
    public function saveGroup(Request $request)
    {
        $user = Auth::user();
        $params = $request->all();
        $postowner = PostcOwner::updateOrCreate([ 'user_id' => $user->id, 'infs_account_id' => $params['accountID'], 'infs_person_id' => $params['contact'], 'owner_name' => $params['infsName'] ], ['status' => 1]);
    
        $data = array(
                'postc_owner_id' =>$postowner->id,
                'status' => 1,
                'postc_country_code' => $params['country']
            );
            
        if ($params['areagroup'] == 'radius_around_postcode') {
            $data['postc_code'] = $params['postcode'];
            $data['postc_radius'] = $params['kmvalue'];
            $data['postc_units'] = $params['unit'];
            $data['match_type'] = 1;
        } else {
            $data['match_type'] = 2;
            $data['postc_list'] = $params['areagrouptext'];
        }
        
        PostcOwnerGroups::create($data);
        
        $postOwner = PostcOwner::with('group')->where('infs_account_id', $params['accountID'])->where('user_id', $user->id)->where('status', 1)->get();
        $infs_account_id = $params['accountID'];
        $owners = view('scripts.partial.postOwner', compact('postOwner', 'infs_account_id'))->render();
        return array( 'status' => 'success', 'message' => 'Group added successfully.', 'response' => $owners );
    }
    
    public function editOwner(Request $request)
    {
        $user = Auth::user();
        $params = $request->all();
        
        $ownerGroups = PostcOwner::where('user_id', $user->id)->where('infs_account_id', $params['accountID'])->where('id', $params['id'])->first();
        
        $contacts = $this->infusionSoftService->getUsers($user->id, $params['accountID']);
       
        $countries = PostcCountries::where('country_code', '<>', '')->get();
        
        $owners = view('scripts.partial.updateOwner', compact('contacts', 'countries', 'ownerGroups'))->render();
        return array( 'status' => 'success', 'response' => $owners );
    }
    
    public function editOwnerGroup(Request $request)
    {
        $params = $request->all();
        
        $ownerGroup = PostcOwnerGroups::where('id', $params['id'])->where('postc_owner_id', $params['postc_owner_id'])->first();
        if (count($ownerGroup) == 0) {
            return array( 'status' => 'failed', 'message' => 'Owner group not found.' );
        }
        return array( 'status' => 'success', 'response' => $ownerGroup );
    }

    public function updateOwner(Request $request)
    {
        $params = $request->all();
        
        $postowner = PostcOwner::where('id', $params['id'])->update([ 'infs_account_id' => $params['accountID'], 'infs_person_id' => $params['contact'], 'owner_name' => $params['infsName'] ]);
        
        if (isset($params['onwer_group_id'])) {
            $data = array(
                    'postc_country_code' => $params['country']
                );
                
            if ($params['areagroup'] == 'radius_around_postcode') {
                $data['postc_code'] = $params['postcode'];
                $data['postc_radius'] = $params['kmvalue'];
                $data['postc_units'] = $params['unit'];
                $data['match_type'] = 1;
                
                $data['postc_list'] = '';
            } else {
                $data['postc_code'] = '';
                $data['postc_radius'] = '';
                $data['postc_units'] = '';
                
                $data['match_type'] = 2;
                $data['postc_list'] = $params['areagrouptext'];
            }
            if ($params['onwer_group_id'] == 'new') {
                $data['postc_owner_id'] = $params['id'];
                PostcOwnerGroups::create($data);
            } else {
                PostcOwnerGroups::where('id', $params['onwer_group_id'])->update($data);
            }
        }
        
        $postOwner = PostcOwner::with('group')->where('infs_account_id', $params['accountID'])->where('user_id', Auth::id())->where('status', 1)->get();
        $postOwnergroup = PostcOwnerGroups::where('postc_owner_id', $params['id'])->where('status', 1)->get();
        
        $owners = view('scripts.partial.postOwner', compact('postOwner'))->render();
        $ownersGroup = view('scripts.partial.listOwnerGroup', compact('postOwnergroup'))->render();
        
        return array( 'status' => 'success', 'message' => 'Group updated successfully.', 'response' => $owners, 'response2' => $ownersGroup );
    }
    
    public function deleteOwner(Request $request)
    {
        $params = $request->all();

        $postowner = PostcOwner::where('id', $params['id'])->first();
        if (count($postowner) == 0) {
            return array( 'status' => 'failed', 'message' => 'Owner not found.' );
        }

        $postowner = PostcOwner::where('id', $params['id'])->update([ 'status' => 0 ]);
        PostcOwnerGroups::where('postc_owner_id', $params['id'])->update([ 'status' => 0 ]);

        $postOwner = PostcOwner::with('group')->where('infs_account_id', $params['accountID'])->where('user_id', Auth::id())->where('status', 1)->get();
        $owners = view('scripts.partial.postOwner', compact('postOwner'))->render();

        return array( 'status' => 'success', 'message' => 'Owner deleted successfully.', 'response' => $owners );
    }

    public function deleteOwnerGroup(Request $request)
    {
        $params = $request->all();

        $postowner = PostcOwnerGroups::where('id', $params['owner_gourp_id'])->first();
        if (count($postowner) == 0) {
            return array( 'status' => 'failed', 'message' => 'Group not found.' );
        }

        PostcOwnerGroups::where('id', $params['owner_gourp_id'])->update([ 'status' => 0 ]);

        $postOwnergroup = PostcOwnerGroups::where('postc_owner_id', $params['owner_id'])->where('status', 1)->get();
        $owners = view('scripts.partial.listOwnerGroup', compact('postOwnergroup'))->render();

        return array( 'status' => 'success', 'message' => 'Group deleted successfully.', 'response' => $owners );
    }

    public function reAssignContactOwner(Request $request)
    {
        $params = $request->all();

        $infs_account_id = (isset($params["infs_account_id"]))?$params["infs_account_id"]:"";

        if ($infs_account_id == "") {
            return array( 'status' => 'fail', 'message' => 'INFS Account missing.', 'response' => "" );
        }

        $user = \Auth::user();
        if (!isset($user->id)) {
            return array( 'status' => 'fail', 'message' => "User Authentication failed.", 'response' => "" );
        }

        \App\PostcOwnerSync::updateOrCreate(["user_id"=>$user->id,"infs_account_id"=>$infs_account_id, "status"=>0, "start_date_time"=>"0000-00-00 00:00:00", "finish_date_time"=>"0000-00-00 00:00:00", "contacts_updated"=>0, "opp_updated"=>0]);
        return array( 'status' => 'success', 'message' => "Your contact owner assignment has been queued. You will get an email after the process is completed.", 'response' => "" );
    }

    public function processOwnerAssignmentCron()
    {
        $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
        $post_sync_data = \App\PostcOwnerSync::where("status", 0)->first();
        if (!isset($post_sync_data->id)) {
            return ;
        }

        #update the start date time
        \App\PostcOwnerSync::where("id", $post_sync_data->id)->update(["status"=>1, "start_date_time"=>date("Y-m-d H:i:s")]);

        $status = $obj->ReAssignPostcBasedOwner($post_sync_data->infs_account_id, $post_sync_data->user_id);

        #update the start date time
        \App\PostcOwnerSync::where("id", $post_sync_data->id)->update(["status"=>2, "finish_date_time"=>date("Y-m-d H:i:s"), "contacts_updated"=>$status["contacts_updated"], "opp_updated"=>$status["opp_updated"]]);

        $content = "The contacts inside your specific infusionsoft account have now been completed for owner assignment.<br><br>";
        $content .= "<b>Total contacts updated: </b>".$status["contacts_updated"]."<br>";
        $content .= "<b>Total Opportunity updated: </b>".$status["opp_updated"]."<br>";

        $user_data = \App\User::where("id", $post_sync_data->user_id)->first();
        $email = $user_data->email;

        #send an email
        \Mail::send('emails.tagEmail', [ 'content' => $content ], function ($message) use ($email) {
            $message->from('help@fusedtools.com', 'FusedTools');
            $message->to(\CommanHelper::notifyEmails())->bcc("help@fusedtools.com")->subject('Owner Reassignment Complete');
        });
    }

    public function infsContactFields(Request $request)
    {
        $params = $request->all();
        $user = Auth::user();

        $infsAccount = InfsAccount::where('user_id', $user->id)->where('id', $params['accountID'])->first();
        $xeroAccount = XeroAccounts::where('user_id', $user->id)->where('id', $params['xeroID'])->first();

        if (empty($infsAccount) || empty($xeroAccount)) {
            return array( 'status' => 'failed', 'message' => 'Account not found.' );
        }
        if (Carbon::createFromTimestamp($xeroAccount->oauth_expires_in)->toDateTimeString() < Carbon::now()) {
            return array( 'status' => 'failed', 'message' => 'This xero account has expired, Please reauth this account.' );
        }

        $customFields = $this->infusionSoftService->getContactCustomFields($user->id, $params['accountID'], $params['xeroID']);
        $contactFields = config('infusionsoft.infusionsoftFields');

        if (count($customFields)) {
            foreach ($customFields as $res) {
                $fieldname = "_".$res['Name'];
                $contactFields[$fieldname] = 'String';
            }
        }
        ksort($contactFields, SORT_STRING);

        $xeroSettings = xeroInvoiceSetting::where('user_id', $user->id)->where('xero_id', $params['xeroID'])->where('account_id', $params['accountID'])->first();
        $xero_settings = "{}";
        if (count($xeroSettings) > 0) {
            $xero_settings = json_decode($xeroSettings->settings, true);
        }

        $this->xero_service = new XeroService();
        $saleAccounts = $this->xero_service->getAccounts($xeroAccount->app_id);
        
        $response = view('scripts.partial.xeroContacts', compact('contactFields', 'saleAccounts', 'xero_settings'))->render();
        
        return array( 'status' => 'success', 'response' => $response );
    }

    public function saveXeroInvoice(Request $request)
    {
        $this->validate($request, [
            "accountID" => "required",
            "xeroID" => "required"
        ]);
        $user = Auth::user();
        $params = $request->all();
        
        $settings = array(
                'xero_field' => $params['xeroid'],
                'company' => $params['compname'],
                'invoice_status' => $params['invoice_status'],
                'sale_account' => $params['sale_account'],
                'infs_fields' => $params['infs_fields'],
            );
            
        xeroInvoiceSetting::updateOrCreate(['user_id' => $user->id, 'account_id' => $params['accountID'], 'xero_id' => $params['xeroID']], [ 'user_id' => $user->id, 'account_id' => $params['accountID'], 'xero_id' => $params['xeroID'] , 'settings' => json_encode($settings)]);
        
        return array( 'status' => 'success', 'message' => 'Xero invoice settings saved successfully.' );
    }

    public function xeroCronPartial(Request $request)
    {
        $params = $request->all();
        $user = Auth::user();

        $infsAccount = InfsAccount::where('user_id', $user->id)->where('id', $params['accountID'])->first();
        $xeroAccount = XeroAccounts::where('user_id', $user->id)->where('id', $params['xeroID'])->first();

        if (empty($infsAccount) || empty($xeroAccount)) {
            return array( 'status' => 'failed', 'message' => 'Account not found.' );
        }
        if (Carbon::createFromTimestamp($xeroAccount->oauth_expires_in)->toDateTimeString() < Carbon::now()) {
            return array( 'status' => 'failed', 'message' => 'This xero account has expired, Please reauth this account.' );
        }

        $customFields = $this->infusionSoftService->getContactCustomFields($user->id, $params['accountID'], $params['xeroID']);
        $contactFields = config('infusionsoft.infusionsoftFields');

        if (count($customFields)) {
            foreach ($customFields as $res) {
                $fieldname = "_".$res['Name'];
                $contactFields[$fieldname] = 'String';
            }
        }
        ksort($contactFields, SORT_STRING);

        $xeroSettings = \App\XeroCronSync::where('user_id', $user->id)->where('xero_id', $params['xeroID'])->where('infusionsoft_account_id', $params['accountID'])->first();
        $xero_settings = "{}";
        if (count($xeroSettings) > 0) {
            $xero_settings = json_decode($xeroSettings->settings, true);
        }

        $this->xero_service = new XeroService();
        $saleAccounts = $this->xero_service->getAccounts($xeroAccount->app_id);

        $response = view('scripts.partial.xeroCron', compact('contactFields', 'saleAccounts', 'xero_settings'))->render();

        return array( 'status' => 'success', 'response' => $response );
    }
    
    public function saveXeroCron(Request $request)
    {
        $this->validate($request, [
            "accountID" => "required",
            "xeroID" => "required"
        ]);
        $user = Auth::user();
        $params = $request->all();

        $settings = array(
                'xero_field' => $params['xero_field'],
                'contact' => $params['contact'],
                'invoice_status' => $params['invoice_status'],
                'tax_status' => $params['tax_status'],
                'sale_account' => $params['sale_account'],
                'infs_fields' => $params['infs_fields'],
            );
        $XeroCronId = XeroCronSync::updateOrCreate(['user_id' => $user->id, 'infusionsoft_account_id' => $params['accountID'], 'xero_id' => $params['xeroID']], ['user_id' => $user->id, 'infusionsoft_account_id' => $params['accountID'], 'xero_id' => $params['xeroID'],'settings' => json_encode($settings), 'status' => $params['status'] ]);

        if (!empty($XeroCronId)) {
            $suncResult = XeroCronSyncResult::where('xero_cron_sync_id', $XeroCronId->id)->whereIn('status', [1,2])->first();
            if ($suncResult) {
                return array( 'status' => 'failed', 'message' => 'Xero cron settings updated.' );
            }
        }

        XeroCronSyncResult::create([ 'xero_cron_sync_id' => $XeroCronId->id, 'status' => 1 ]);
        return array( 'status' => 'success', 'message' => 'Xero cron settings saved successfully.' );
    }
    

    /**
     * Country Based Owner
     */
    public function addOwnerCBO(Request $request)
    {
        $params = $request->all();
        $contacts = $this->infusionSoftService->getUsers(Auth::id(), $params['accountID']);
       
        $countries = PostcCountries::distinct('country_name')->get();
        
        $owners = view('scripts.partial.addOwner', compact('contacts', 'countries'))->render();
        
        return array( 'status' => 'success', 'response' => $owners );
    }


    public function postOwnerCBO(Request $request)
    {
        $params = $request->all();
        $response = array();
        
        $infs_account_id = $params['accountID'];
        
        $postOwner = PostcOwner::with('group')->where('infs_account_id', $infs_account_id)->where('user_id', Auth::id())->where('status', 1)->get();

        $infs = InfsAccount::find($infs_account_id);

        $app_name = $infs->count() > 0 ? $infs['name'] : null;
        
        $owners = view('scripts.partial.postOwner', compact('postOwner', 'infs_account_id'))->render();
        return array( 'status' => 'success', 'response' => $owners, 'app_name' => $app_name );
    }
}
