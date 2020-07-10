<?php
namespace App\Http\Controllers;

use App\Services\InfusionSoftService;
use App\User;
use App\InfsSync;
use App\UserUsageTasks;
use Auth;
use Infusionsoft;
use App\InfsAccount;
use App\ScriptHttpPosts;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Infusionsoft\Token as InfusionsoftToken;
use App\CompanyContactMap;
use App\ContactCompanyLink;
use Validator;

/**
 * Class InfusionSoftScriptsController
 * @package App\Http\Controllers
 */
class InfusionSoftScriptsController extends Controller
{
    private $authUser 	 = '';
    protected $infusionSoftService;

    public function __construct(InfusionSoftService $infusionSoftService)
    {
        // $this->authUser = Auth::user();
        $this->infusionSoftService = $infusionSoftService;
    }
    
    public function scripts(Request $request)
    {
        $params = $request->all();
        $mode = $params['mode'] ?: '';
        $account = $params['app'] ?: '' ;
        $contactId = $params['contactId'] ?: '' ;
        $FuseKey = $request['fusekey'] ?: '' ;
        $FuseKey = $request['FuseKey'] ?: $FuseKey ;
        
        $user = User::where('FuseKey', $FuseKey)->first();
        if (empty($user)) {
            return $output = array('response' => 'fail','message' => 'Fuse user not found, Please give real FuseKey.');
        } elseif (empty($mode)) {
            $output = array('response' => 'fail','message' => 'Mode field is not set.');
        } elseif (empty($account)) {
            $output = array('response' => 'fail','message' => 'Account field is not set.');
        } elseif (empty($contactId)) {
            $output = array('response' => 'fail','message' => 'Contact Id field is not set.');
        } elseif (empty($FuseKey)) {
            $output = array('response' => 'fail','message' => 'FuseKey field is not set.');
        } else {
            if (trim($mode) == 'stageMove' || trim($mode) == 'stagemove') {
                $output = $this->stageMove($user, $params);
            } elseif (trim($mode) == 'store_product_names' || trim($mode) == 'store_product_names') {
                $output = $this->productToField($user, $params);
            } elseif (trim($mode) == 'copy_values' || trim($mode) == 'copy_values') {
                $output = $this->copyFieldValue($user, $params);
            } elseif (trim($mode) == 'increment_field' || trim($mode) == 'increment_field') {
                $output = $this->incrementFields($user, $params);
            } elseif (trim($mode) == 'update_card' || trim($mode) == 'update_card') {
                $output = $this->updateCreditCard($user, $params);
            } elseif (trim($mode) == 'calculate_date' || trim($mode) == 'calculate_date') {
                $output = $this->calculateDate($user, $params);
            } elseif (trim($mode) == 'country_owner' || trim($mode) == 'country_owner') {
                $output = $this->countryBasedOwner($user, $params);
            } else {
                return array('response' => 'fail','message' => 'Please set mode field properly.');
            }
        }
        
        if (isset($output['result'])) {
            $this->fuseHttpPost($output);
        }
        
        if (isset($output['response']) && $output['response'] == 'success') {
            UserUsageTasks::updateOrCreate(['user_id' => $user->id, 'account' => $params['app']], ['tasks_used' => (int)$output['tasks_used']+1 ]);
        }
        
        // $this->sendNotification($user->fullname, $user->email, $output['message']);
        return $output;
    }

    /**
     * Sync function that will handle Company Contact Sync
     *
     * @param Request $request
     * @return array
     */
    public function sync(Request $request, $account)
    {
        \Log::info('Request received: '.print_r($request->all(), true));
        $data = $request->all();

        // check the headers in the request to verify the hook subscription
        $this->infusionSoftService->hookAutoVerify();

        // get the infusionsoft account and user
        $infs_account = InfsAccount::where('name', $account)
                ->first();

        if (!$infs_account) {
            return array('response' => 'fail','message' => 'Infusionsoft not found.');
        }

        $user = $infs_account->user()->first();

        // get the data from the company mapping in an array
        $cc_mappings = $infs_account->companyContactMap()->pluck('company_field_map')->toArray();

        if (empty($cc_mappings) && empty($cc_mappings_cf)) {
            return array('response' => 'fail','message' => 'No mapping found.');
        }
        
        // filter the events process
        // 1 - company edit / add
        if (
            array_key_exists('event_key', $data) &&
            ($data['event_key'] == 'company.add' || $data['event_key'] == 'company.edit')
        ) {
            if (array_key_exists('object_keys', $data)) {
                foreach ($data['object_keys'] as $company) {
                    $company_id = $company['id'];

                    // get company fields and details from INFS
                    $company_details = $this->infusionSoftService->getCompanyDetails($user->id, $infs_account->id, $company_id, $cc_mappings);
                    $company_data = $company_details[0];

                    // map the data for update in the contact fields
                    $company_mapping = $this->processCompToContMap($company_data, $infs_account);
                    \Log::info('COMPANY MAPPING: '.print_r($company_mapping, true));

                    // get contacts that are associated with the company
                    $contacts = $this->infusionSoftService->getCompanyContactIds($user->id, $infs_account->id, $company_id);
                    array_shift($contacts);
                    \Log::info('CONTACTS TO BE SYNC: '.print_r($contacts, true));

                    // if the contacts are existing and the contact mapping is formatted, sync each one of them
                    if (($contacts || !empty($contacts)) && !is_null($company_mapping)) {
                        foreach ($contacts as $contact) {
                            $this->infusionSoftService->companyContactSync($user->id, $infs_account->id, $contact['Id'], $company_mapping);
                        }
                    }
                }
            }
        }

        // 2 - contact add
        if (array_key_exists('event_key', $data) &&
            $data['event_key'] == 'contact.add'
        ) {
            if (array_key_exists('object_keys', $data)) {
                foreach ($data['object_keys'] as $contact) {
                    $contact_id = $contact['id'];

                    // check if the contact id is the link table
                    $contact_link = ContactCompanyLink::where('contact_id', $contact_id)
                        ->first();

                    // if linked no need to update it already
                    if ($contact_link) {
                        return array('response' => 'success','message' => 'Contact already linked');
                    }
                    ;
                    // if not get the contact details
                    $contact = $this->infusionSoftService->getContacts($user->id, $infs_account->id, $contact_id, ['Id','CompanyID']);
                    $contact = $contact[0];

                    if (empty($contact)) {
                        return array('response' => 'failed','message' => 'No Contact found');
                    }

                    $company_details = $this->infusionSoftService->getCompanyDetails($user->id, $infs_account->id, $contact['CompanyID'], $cc_mappings);
                    $company_data = $company_details[0];

                    // map the data for update in the contact fields
                    $company_mapping = $this->processCompToContMap($company_data, $infs_account);

                    // sync the company contact
                    $this->infusionSoftService->companyContactSync($user->id, $infs_account->id, $contact_id, $company_mapping);
                }
            }
        }

        return array('response' => 'success', 'message' => 'Sync Finished');
    }

    /**
     * Bulk Contacts Tagging
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function contactBulkTagging(Request $request)
    {
        // get the required variables
        $user = Auth::user();
        $infs = $user->infsAccounts()->first();
        $query_fd = $request->input('qfield');
        $lists = $request->input('listOfData');
        $lists = explode(',', rtrim($lists, ","));
        $tags = $request->input('tags');

        // get the contact Ids
        $contact_ids = [];

        // process each filter for the data
        foreach ($lists as $list) {

            // format the query filter
            $query = array($query_fd => $list);

            // get the list of contacts
            $contacts = $this->infusionSoftService->getContactIdsByFilter($user->id, $infs->id, $query);

            \Log::info('Contacts List: '.print_r($contacts, true));

            // merge contact ids
            if (count($contacts) > 0) {
                foreach ($contacts as $contact) {
                    // added this for a strange behavoir about INFS returning filter ID's
                    if (preg_match('/ID/', $query_fd) && $list == $contact['Id']) {
                        continue;
                    }
                    $contact_ids[] = $contact['Id'];
                }
            }
        }

        // process each tags for the data
        if (count($contact_ids) > 0) {
            foreach ($tags as $tag) {
                $result = $this->infusionSoftService->bulkAssignTag($user->id, $infs->id, $contact_ids, $tag);
                \Log::info('BULK TAGGING RESULT: '.print_r($result, true));
            }
        }

        $request->session()->flash('success', 'Bulk Tagging Finished');
        return redirect()->back();
    }

    /**
     * Subscribe for the synchronization
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncSubscribe(Request $request)
    {
        $account_id = $request->input('infsAcct');
        $company_edit = $request->input('company_edit', 0);
        $company_add = $request->input('company_add', 0);
        $contact_add = $request->input('contact_add', 0);

        $account = InfsAccount::find($account_id);

        if (!$account) {
            $request->session()->flash('danger', 'INFS Account not found');
            return redirect()->back();
        }

        $sync = InfsSync::where('infs_account_id', $account_id)->first();

        if (!$sync) {
            $account->infsSync()->save(new InfsSync([
                'company_add' => $company_add,
                'company_edit' => $company_edit,
                'contact_add' => $contact_add,
            ]));
        } else {
            if ($company_edit != 0) {
                $sync->company_edit = 1;
            }
            if ($company_add != 0) {
                $sync->company_add = 1;
            }
            if ($contact_add != 0) {
                $sync->contact_add = 1;
            }
            $sync->update();
        }

        $url = url('/') . '/sync/' . $account->name;
        if ($company_add == 1) {
            $this->infusionSoftService->infusionsoftHookSubscription(Auth::id(), $account_id, 'company.add', $url);
        }
        if ($company_edit == 1) {
            $this->infusionSoftService->infusionsoftHookSubscription(Auth::id(), $account_id, 'company.edit', $url);
        }
        if ($contact_add == 1) {
            $this->infusionSoftService->infusionsoftHookSubscription(Auth::id(), $account_id, 'contact.add', $url);
        }

        $request->session()->flash('success', 'INFS Account subscribed');
        return redirect()->back();
    }


    /**
     * Unsubscribe sync for the accounts
     *
     * @param Request $request
     * @return array
     */
    public function syncUnsubscribe(Request $request)
    {
        $account_id = $request->input('infsAcct');
        $account = InfsAccount::find($account_id);

        if (!$account) {
            return [
                    'status' => 'failed',
                    'message' => 'INFS Account not found.'
            ];
        }

        $sync = InfsSync::where('infs_account_id', $account_id)->first();

        if ($sync) {
            if ($sync->company_add == 1) {
                $this->infusionSoftService->infusionsoftHookDeleteSubscription(Auth::id(), $account_id, 'company.add');
            }
            if ($sync->company_edit == 1) {
                $this->infusionSoftService->infusionsoftHookDeleteSubscription(Auth::id(), $account_id, 'company.edit');
            }
            if ($sync->contact_add == 1) {
                $this->infusionSoftService->infusionsoftHookDeleteSubscription(Auth::id(), $account_id, 'contact.add');
            }
            $sync->delete();
        }

        return [
                'status' => 'success',
                'message' => 'INFS Account unsubscribed'
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getCustomFields(Request $request)
    {
        $fields = [];
        \Log::info(print_r($request->all(), true));
        // get the account data from the request object
        $account_id = $request->input('infs_account_id');
        $form = $request->input('form');

        // get the fields from the config file
        $cmfield = $form == '-1' ? config('columns')['contact'] : config('columns')['company'];

        // merge it with the fields
        $fields = array_merge($fields, $cmfield);
        $table = $form == '-1' ? 'Contact' : 'Company';

        // get the custom fields from the INFS API
        $results = $this->infusionSoftService->getInfsCustomField(Auth::id(), $account_id, $table);
        //\Log::info(print_r($results,true));

        if ($results) {
            //\Log::info(json_encode($results));
            foreach ($results as $result) {
                //\Log::info(json_encode($result));
                $fields[] = '_'.$result['Name'];
            }
        }

        return [
            'status' => 'success',
            'fields' => $fields
        ];
    }

    /**
     * Get INFS Account Group Names (Tags)
     *
     * @param Request $request
     * @return array
     */
    public function getTagFields(Request $request)
    {
        $tags = [];
        \Log::info(print_r($request->all(), true));

        $account_id = $request->input('infs_account_id');

        $results = $this->infusionSoftService->getTags(Auth::id(), $account_id);

        //\Log::info('TAGS: '.print_r($results,true));

        if ($results && count($results) > 0) {
            foreach ($results as $result) {
                //\Log::info(json_encode($result));
                $tags[] = [ 'id' => $result['Id'], 'name' => $result['GroupName'] ];
            }
        }

        return [
            'status' => 'success',
            'fields' => $tags
        ];
    }

    public function stageMove($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('user_id', $user->id)->where('account', $account)->first();
        
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['stageid']) && empty($params['stageid'])) {
            return $output = array('response' => 'fail','message' => 'stageid field is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                return $output;
            }
            $accountId = $getAccount->id;
        }
        
        if ($accountId) {
            $contactId = $params['contactId'];
            $stageId = $params['stageid'];
            
            $output = $this->infusionSoftService->stageMove($stageId, $contactId, $user->id, $accountId);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'stageId' => $stageId );
            $output['mode'] = 'stageMove';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }

    public function productToField($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('user_id', $user->id)->where('account', $account)->first();
        
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['method']) && empty($params['method'])) {
            $output = array('response' => 'fail','message' => 'Method field is not set.');
        } elseif (!isset($params['fieldto']) && empty($params['fieldto'])) {
            $output = array('response' => 'fail','message' => 'fieldto field is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                $this->sendNotification(null, $user->email, $output['message']);
                return $output;
            }
            $accountId = $getAccount->id;
        }
        
        if ($accountId) {
            $contactId = $params['contactId'];
            $fields = $params['fieldto'];
            $method = $params['method'];
            
            $output = $this->infusionSoftService->productToField($contactId, $user->id, $fields, $method, $accountId);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'fieldto' => $fields );
            $output['mode'] = 'store_product_names';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }
    
    public function copyFieldValue($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('account', $account)->first();
        
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['fieldfrom']) && empty($params['fieldfrom'])) {
            $output = array('response' => 'fail','message' => 'Fieldfrom is not set.');
        } elseif (!isset($params['fieldto']) && empty($params['fieldto'])) {
            $output = array('response' => 'fail','message' => 'Fieldto is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                $this->sendNotification(null, $user->email, $output['message']);
                return $output;
            }
            $accountId = $getAccount->id;
        }
        
        if ($accountId) {
            $contactId = $params['contactId'];
            $method = $params['method'];
            $fieldfrom = $params['fieldfrom'];
            $fieldto = $params['fieldto'];
            
            $output = $this->infusionSoftService->copyFieldValue($user->id, $contactId, $fieldfrom, $fieldto, $method, $accountId);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'method' => $method, 'fieldfrom' => $fieldfrom, 'fieldto' => $fieldto );
            $output['mode'] = 'copy_values';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }

    public function incrementFields($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('account', $account)->first();
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['amount']) && empty($params['amount'])) {
            $output = array('response' => 'fail','message' => 'Amount field is not set.');
        } elseif (!isset($params['fieldto']) && empty($params['fieldto'])) {
            $output = array('response' => 'fail','message' => 'fieldto is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                $this->sendNotification(null, $user->email, $output['message']);
                return $output;
            }
            $accountId = $getAccount->id;
        }
    
        if ($accountId) {
            $contactId = $params['contactId'];
            $amount = $params['amount'];
            $field = $params['fieldto'];
            $output = $this->infusionSoftService->incrementFields($user->id, $contactId, $field, $amount, $accountId);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'amount' => $amount, 'fieldto' => $field);
            $output['mode'] = 'increment_field';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }
    
    public function updateCreditCard($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('account', $account)->first();
        
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['mechant_id']) && empty($params['mechant_id'])) {
            $output = array('response' => 'fail','message' => 'mechant_id field is not set.');
        } elseif (!isset($params['update_userSubscriptions']) && empty($params['update_userSubscriptions'])) {
            $output = array('response' => 'fail','message' => 'update_userSubscriptions field is not set.');
        } elseif (!isset($params['rebill_userSubscriptions']) && empty($params['rebill_userSubscriptions'])) {
            $output = array('response' => 'fail','message' => 'rebill_userSubscriptions field is not set.');
        } elseif (!isset($params['only_active']) && empty($params['only_active'])) {
            $output = array('response' => 'fail','message' => 'only_active field is not set.');
        } elseif (!isset($params['rebill_orders']) && empty($params['rebill_orders'])) {
            $output = array('response' => 'fail','message' => 'rebill_orders field is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                $this->sendNotification(null, $user->email, $output['message']);
                return $output;
            }
            $accountId = $getAccount->id;
        }

        if ($accountId) {
            $contactId = $params['contactId'];
            $merchant = $params['mechant_id'];
            $update_subs = is_numeric($params['update_userSubscriptions'])?$params['update_userSubscriptions']:1;
            $rebill_orders = is_numeric($params['rebill_orders'])?$params['rebill_orders']:0;
            $rebill_subs = is_numeric($params['rebill_userSubscriptions'])?$params['rebill_userSubscriptions']:0;
            $only_active = is_numeric($params['only_active'])?$params['only_active']:1;
            
            $output = $this->infusionSoftService->updateCreditCard($user->id, $contactId, $merchant, $accountId, $update_subs, $rebill_orders, $rebill_subs, $only_active);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'mechant_id' => $merchant, 'contactId' => $contactId, 'update_subs' => $update_subs, 'rebill_orders' => $rebill_orders, 'rebill_subs' => $rebill_subs, 'only_active' => $only_active);
            $output['mode'] = 'update_card';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }
    
    public function calculateDate($user, $params)
    {
        $accountId = '';
        $output = '';
        
        $account = $params['app'].'.infusionsoft.com';
        $getAccount = InfsAccount::where('account', $account)->first();
        
        if (empty($getAccount)) {
            $output = array('response' => 'fail','message' => 'Infusionsoft account not found.');
            return $output;
        } elseif (!isset($params['fieldto']) && empty($params['fieldto'])) {
            $output = array('response' => 'fail','message' => 'fieldto field is not set.');
        } elseif (!isset($params['startdate']) && empty($params['startdate'])) {
            $output = array('response' => 'fail','message' => 'startdate field is not set.');
        } elseif (!isset($params['add_time']) && empty($params['add_time'])) {
            $output = array('response' => 'fail','message' => 'add_time field is not set.');
        } else {
            $plan = $user->userSubscription->plan;
            $userLimit = \CommanHelper::userLimit($user, $plan, $params['app']);
            if (isset($userLimit['denied'])) {
                $output = array('response' => 'fail','message' => 'You have reached your account limit.');
                $this->sendNotification(null, $user->email, $output['message']);
                return $output;
            }
            $accountId = $getAccount->id;
        }

        if ($accountId) {
            $contactId = $params['contactId'];
            $fieldto = $params['fieldto'];
            $startdate = $params['startdate'];
            $add_time = $params['add_time'];
            
            $output = $this->infusionSoftService->calculateDate($user->id, $accountId, $contactId, $fieldto, $startdate, $add_time);
            
            $output['contactId'] = $contactId;
            $output['post_data'] = array( 'fieldto' => $fieldto, 'startdate' => $startdate, 'add_time' => $add_time );
            $output['mode'] = 'calculate_date';
            $output['user_id'] = $user->id;
            $output['accountId'] = $accountId;
            $output['tasks_used'] = $userLimit['requestCount'];
        }
        return $output;
    }

    public function fuseHttpPost($data)
    {
        $response_data = json_encode($data['result']);
        ScriptHttpPosts::create([ 'user_id' => $data['user_id'], 'infs_account_id' => $data['accountId'], 'contactId' => $data['contactId'], 'mode' => $data['mode'], 'status' => $data['response'], 'post_data' => json_encode($data['post_data']), 'response_data' => $response_data ]);
    }
    
    public function sendNotification($name, $userMail, $response)
    {
        $data = [
            'content' => $response,
            'name' => $name
        ];
        \Mail::send('emails.sendMail', $data, function ($message) use ($userMail) {
            $message->from('help@fusedtools.com', 'Fuesdtools');
            $message->to(\CommanHelper::notifyEmails())->bcc("help@fusedtools.com")->subject('Fuesdtools script notification.');
        });
    }

    /**
     * Company to Contact data mapping
     *
     * @param
     *      $data - array data from the company details coming from the company mapping defined by the user
     *      $infs - infusion account object
     * @return
     *      array - the final mapping that will be updated for the contact
     */
    public function processCompToContMap($data, $infs)
    {
        $cc_map = [];
        $cc_mappings = $infs->companyContactMap()->get();

        if ($cc_mappings) {
            foreach ($cc_mappings as $cc_mapping) {
                if (array_key_exists($cc_mapping->company_field_map, $data)) {
                    $cc_map[$cc_mapping->contact_field_map] = $data[$cc_mapping->company_field_map];
                }
            }
        }
        \Log::info('This is the company contact mapping data: '.json_encode($cc_map));
        return $cc_map;
    }



    /**
     * Country Based Owner
     */
    
    public function countryBasedOwner($user, $params)
    {
        $logger = new \App\Logger\Logger(new \App\Logger\LogToFile);

        $fuse_key   = $params['FuseKey'];
        $app_name   = $params['app'];
        $country    = $params['Country'];
        $conID      = $params['contactId'];
        $include_closed = $params['include_closed'] ?? 0;
        $skip_opps = $params['skip_opps'] ?? 0;


        $user_res = \App\User::where("FuseKey", $fuse_key)->first();
        if (!isset($user_res->id)) {
            return "User not found.";
        }

        $user_id = $user_res->id;

        #find the infusionsoft account ID using app_name
        $infs_res = \App\InfsAccount::where("user_id", $user_id)->where("name", $app_name)->first();
        if (!isset($infs_res->id)) {
            return "Invalid infusionsoft account.";
        }

        $infs_account_id = $infs_res->id;

        //check for postcode list results first as it requires less db queries
        $result_list = \App\CountryOwner::leftJoin('country_owner_groups as COG', 'country_owner.id', '=', 'COG.country_owner_id')
            ->leftJoin('infs_countries as IC', 'COG.infs_country_id', '=', 'IC.id')
            ->where("country_owner.user_id", $user_id)
            ->where("country_owner.infs_account_id", $infs_account_id)
            ->where("country_owner.status", 1)
            ->where("COG.status", 1)
            ->where("IC.country_name", $country)
            ->select('COG.*', 'country_owner.infs_person_id')
            ->first();

        if (isset($result_list) && count((array) $result_list) > 0) {
            $owner = $result_list->infs_person_id;

            $logger->writeDown('Found an owner from our list: ' . print_r($owner, true));
        } else {
            $country_owner = \App\CountryFallbackOwner::where('user_id', $user_id)->where('infs_account_id', $infs_account_id)->get();
            if (count($country_owner) > 0) {
                $owner = $country_owner[0]->fallback_owner_id;
                $logger->writeDown('Fallback Owner Executed: ' . print_r($owner, true));
            } else {
                $logger->writeDown('Fallback Owner Executed (BUT NO FALLBACK OWNER FOUND): ' . print_r($owner, true));
                return 'No Owner Found';
            }
        }

       
        if (isset($owner)) {

            #create infusionsoft object here
            $this->infusionSoftService->checkIfRefreshTokenHasExpired($user_id, $infs_account_id);

            $con_owner = $this->infusionSoftService->updateDataInInfusionSoft("Contact", $conID, ['OwnerID' => $owner]);

            $logger->writeDown('Infusionsoft Update Data: (Contact ID: ' . $conID . ', Owner ID: '.$owner.')');
            
            //Use the above result for finding open Lead
            $opps = $this->infusionSoftService->fetchDataFromINFSRecursive("Lead", 1000, ['ContactID' => $conID], ["Id", "StageID"], "Id", true);

            $logger->writeDown('Infusionsoft Lead Data: (Contact ID: ' . $conID . ')');
            
            if (is_array($opps) && count($opps) >= 1 && $skip_opps == 0) { //JAMES ADDED VARIABLE

                $opps_stages = [];
                if ($include_closed == 0) {
                    //Fetch OPEN Lead stage using rest service
                    $opps_stages = $this->infusionSoftService->fetchOppsStages();
                }

                foreach ($opps as $opp) {
                    if ($include_closed == 0 && !in_array($opp["StageID"], $opps_stages)) {
                        continue;
                    }

                    $opps = $this->infusionSoftService->updateDataInInfusionSoft("Lead", $opp['Id'], array('UserID' => $owner));
                }#end of loop
            }
        } else {
            $logger->writeDown('NO OWNER FOUND');
            return 'No Owner Found.';
        }
    }
}
