<?php

namespace App\Http\Controllers;

use URL;
use App\Helpers\CsvStatus;
use App\Helpers\Helpers;
use App\Helpers\TokenFeatureCount;
use App\UserSubscription;
use App\Http\Requests\CsvImport\Step1StoreFormRequest;
use App\Http\Requests\CsvImport\Step2StoreFormRequest;
use App\Http\Requests\CsvImport\StepFormRequest;
use App\InfsAccount;
use App\Repositories\CsvImportRecordRepository;
use App\Repositories\InfusionsoftAccountRepository;
use App\Services\InfusionSoftService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\CsvImportRepository;
use Infusionsoft\Infusionsoft;
use Infusionsoft\Token as InfusionsoftToken;
use Symfony\Component\HttpFoundation\ParameterBag;

class CsvImportController extends Controller {

    private $authUser = [];

    protected $infusionsoft_service;

    protected $csv_import_repo;

    public function __construct(InfusionSoftService $infusionsoftService, CsvImportRepository $csvImportRepo)
    {
        $this->middleware('auth');
        // $this->authUser = Auth::user();
        $this->infusionsoft_service = $infusionsoftService;
        $this->csv_import_repo = $csvImportRepo;
    }

    /**
     * Show the csvimports record and a button to add new
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    
        $imports = $this->csv_import_repo->index();

        return view('v2.importCSV.index', compact('imports'));
    }

    /**
     * Creates a new import
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function newImport() {
        // create a new csv import and redirect to step 1

        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::IMPORTING);
        $parameters->set('import_title', $this->csv_import_repo->getDefaultTitle());
        $parameters->set('user_id', Auth::id());

        // set the user step
        $parameters->set('step', 1);

        // create csv import 
        $csv_import = $this->csv_import_repo->store($parameters);

        return redirect(route('step1', ['id' => $csv_import->id]));
    }

    public function stepStart($id) {

        $csv_import = $this->csv_import_repo->find($id);

        $step = $csv_import->step == 3 ? 4 : $csv_import->step;

        if($csv_import->step) {

            return redirect()->route('step'.$step , ['id' => $csv_import->id]);
        }

        return redirect('/csvimport');

    }

    /**
     * Show the the view for step 1
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function step1(StepFormRequest $request, $id) {

        $csv_import = $this->csv_import_repo->find($id);

        $infusionsoft_account_repo = new InfusionsoftAccountRepository;

        $user_id = Auth::id();

        $accounts = $infusionsoft_account_repo->getActiveUserAccount($user_id);

        return view('v2.importCSV.step1', compact('accounts', 'csv_import'));
    }

    /**
     * Process step 1
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postStep1($id, Step1StoreFormRequest $request) {
        $csv_import = $this->csv_import_repo->find($id);
        $user = Auth::user();
        if (!$csv_import) {
            return redirect('/csvimport')
                ->with('error', 'Import not found.');
        }

        // update csv import fields here
        $this->updateCsvImport($id, $request, 2);

        if (isset($request->csv_file)) {
            // get csv helper
            $csv = Helpers::getCsvData($request->file('csv_file'));

            // if something went wrong while parsing the data, show error
            if (!$csv) {
                $this->csv_import_repo->markAsImportError($id);

                return redirect('/csvimport')
                    ->with('error', 'Something went wrong while processing the csv.');
            }

            // get csv data
            $csv_data = $csv->getData();

            if(!$user->hasToken()) {
                return redirect('/csvimport')
                    ->with('error', 'Sorry you don\'t any token left .');
            } 

            $tokenValue = TokenFeatureCount::tokenValue(TokenFeatureCount::TOOL_FEATURES_CSV, $csv_data);

            if($user->tokenCount() < $tokenValue) {
                return redirect('/csvimport')
                ->with('error', 'Sorry you have insufficient token left.');
            }

            // update csv import records
            $this->createCsvImportRecords($csv_data, $csv_import);

            // set csv file name
            $parameters = new ParameterBag;
            $parameters->set('csv_file', $csv->getFileName());

            $this->csv_import_repo->update($csv_import->id, $parameters->all());
        }

        return redirect(route('step2', ['id' => $csv_import->id]));
    }

    /**
     * Show step 2 - field mapping
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View|mixed
     */
    public function step2(StepFormRequest $request, $id) {
        // get csv import
        $csv_import = $this->csv_import_repo->find($id);

        // get header fields from csv
        $csv_fields = $this->csv_import_repo->getCsvHeaderFields($id);

        // get fields from infs
        $infs_fields = $this->getInfsFields($csv_import->account_id);

        if ($infs_fields['company'] instanceof RedirectResponse) {
            return $infs_fields['company'];
        }

        if ($infs_fields['contacts'] instanceof RedirectResponse) {
            return $infs_fields['contacts'];
        }

        // get all csv imports
        $csv_imports = $this->csv_import_repo->index();

        // remove current csv import from the list
        $csv_imports = $csv_imports->reject(function ($csv_import) use ($id) {
            return $csv_import->id == $id || $csv_import->status == CsvStatus::IMPORTING;
        });

        return view('v2.importCSV.step2', compact('csv_fields','infs_fields', 'csv_import', 'csv_imports'));
    }

    /**
     * Apply maps
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postStep2($id, Step2StoreFormRequest $request) {
        // get csv import
        $csv_import = $this->csv_import_repo->find($id);

        // sanitize csv_settings, remove 0 fields
        $csv_settings = Helpers::sanitizeStep2($request);

        // save csv settings to db
        $parameters = new ParameterBag;
        $parameters->set('csv_settings', $csv_settings);
        $parameters->set('step', 3);

        $this->csv_import_repo->update($id, $parameters->all());

        // @todo: change to step 3 if orders is implemented
        return redirect(route('step4', ['id' => $csv_import->id]));
    }

    /**
     * Updates the settings of the csv import
     *
     * @param $id
     * @param Request $request
     * @return \Exception
     */
    public function settingsStep2($id, StepFormRequest $request) {
        // get selected csv import
        $selected_csv_import = $this->csv_import_repo->find($request->selected_import_id);

        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('csv_settings', $selected_csv_import->csv_settings);

        // update import
        $this->csv_import_repo->update($id, $parameters->all());

        return $selected_csv_import;
    }

    /**
     * This is for the orders part, skip for now
     */
    public function step3(StepFormRequest $request) {}

    /**
     * This is for the orders part, skip for now
     */
    public function postStep3(StepFormRequest $request) {}

    /**
     * Show step 4 - Matching and deduplication rules
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function step4(StepFormRequest $request, $id) {
        $csv_import = $this->csv_import_repo->find($id);

        // get csv settings
        $csv_settings = $csv_import->csv_settings;

        // get import results (matching and dedup)
        $import_results = $csv_import->import_results;

        return view('v2.importCSV.step4', compact('csv_settings', 'csv_import', 'import_results'));
    }

    /**
     * Store all the results to the csv import
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postStep4($id, StepFormRequest $request) {
        // get csv import instance
        $csv_import = $this->csv_import_repo->find($id);

        // get all results except the token
        $results = $request->except('_token');

        $parameters = new ParameterBag;
        $parameters->set('import_results', $results);
        $parameters->set('step', 4);

        $this->csv_import_repo->update($id, $parameters->all());

        return redirect(route('step5', ['id' => $csv_import->id]));
    }

    /**
     * Step 5 - Contact tagging
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function step5(StepFormRequest $request, $id) {
        // get csv import instance
        $csv_import = $this->csv_import_repo->find($id);

        // get tags
        $infs_tags = $this->getInfsTags($csv_import->account_id);

        // process tags
        $tags = $this->processTags($infs_tags);

        return view('v2.importCSV.step5', compact('csv_import','tags'));
    }

    /**
     * Process tags and store it
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function postStep5($id, StepFormRequest $request) {
        // get csv import instance
        $csv_import = $this->csv_import_repo->find($id);

        $tags = [];

        if ($request->apply_tags == "yes") {
            $posted_tags = $request->tags;
            $tags_array = explode(",", $posted_tags);
            $tags = $tags_array;
        }

        // append this to the import_results
        $import_results = $csv_import->import_results;
        $import_results['tags'] = $tags;

        $parameters = new ParameterBag;
        $parameters->set('import_results', $import_results);
        $parameters->set('step', 5);

        $this->csv_import_repo->update($id, $parameters->all());

        return redirect(route('step6', ['id' => $csv_import->id]));
    }

    /**
     * Show the last step
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function step6(StepFormRequest $request, $id) {
        // initialize repositories
        $csv_import = $this->csv_import_repo->find($id);

        $infusionsoft_account_repo = new InfusionsoftAccountRepository;

        // mark as completed import
        $this->csv_import_repo->markAsImportFinished($id);

        // get the pending item
        $csv_import_record_repo = new CsvImportRecordRepository;
        $item = $csv_import_record_repo->pending($id)->get();

        // deduct the token 
        $tokenValue = TokenFeatureCount::tokenValue(TokenFeatureCount::TOOL_FEATURES_CSV, $item);
        
        TokenFeatureCount::useToken($tokenValue);




        // get infs account
        $infs_account = $infusionsoft_account_repo->find($csv_import->account_id);

        // generate view data
        $message = 'Import in progress - we will notify you when it is complete.';
        $account = $infs_account->account;

        return view('v2.importCSV.step6', compact('message', 'account'));
    }

    /**
     * Create csv import record
     *
     * @param $csv
     * @param $request
     * @return \Exception
     */
    public function updateCsvImport($id, $request, $step) {
        $parameters = new ParameterBag;
        $parameters->set('import_title', $request->import_title);
        $parameters->set('account_id', $request->account_id);
        $parameters->set('step', $step);

        return $this->csv_import_repo->update($id, $parameters->all());
    }

    /**
     * Create csv import record
     *
     * @param $csv_data
     * @param $csv_import
     */
    public function createCsvImportRecords($csv_data, $csv_import) {
        $csv_import_record_repo = new CsvImportRecordRepository;

        foreach($csv_data as $key => $value) {
            $params = new ParameterBag;

            $params->set('csv_import_id', $csv_import->id);
            $params->set('data', json_encode($value));
            $params->set('status', CsvStatus::CSV_RECORD_PENDING);

            $csv_import_record_repo->store($params);
        }
    }

    public function updateStep(Request $request) {

        $params = new ParameterBag;
        $params->set('step', $request->step);

        $data = $this->csv_import_repo->update($request->id, $params->all());

        return response()->json(array(
            'success' => true
        ));
    }

    /**
     * @todo edmart: refactor, move to infusionsoft service
     * Get fields from infusionsoft
     *
     * @param $account_id
     * @return array
     */
    public function getInfsFields($account_id) {
        // initialize results
        $results = [];

        $infusionsoft = $this->getInfusionsoftAccount($account_id);

        // get infs company fields
        $infs_company_fields = config('infusionsoft.companyFields');
        $form_id = '-6';
        $return = 'Name';
        $results['company'] = $this->getCustomFields($infusionsoft, $infs_company_fields, $form_id, $return);

        // infs contact fields
        $infs_contact_fields = config('infusionsoft.infusionsoftFields');
        $form_id = '-1';
        $results['contacts']= $this->getCustomFields($infusionsoft, $infs_contact_fields, $form_id, $return);

        return $results;
    }

    /**
     * @todo edmart: refactor, move to infusionsoft service
     * Get tags from infs
     *
     * @param $account_id
     * @return mixed
     */
    public function getInfsTags($account_id) {
        $results = [];

        $infusionsoft = $this->getInfusionsoftAccount($account_id);

        $query_fields = array('GroupName' =>'%');
        $returnFields = array('Id','GroupName');
        $tags_arr = $infusionsoft->data()->query("ContactGroup", 1000, 0, $query_fields, $returnFields, 'Id', true);

        return $tags_arr;
    }

    /**
     * Process the tags from infs
     *
     * @param $infs_tags
     * @return false|string
     */
    public function processTags($infs_tags) {

        $final_arr = [];

        if (count($infs_tags)) {
            foreach ($infs_tags as $arr) {
                $data['id'] = $arr['Id'];
                $data['GroupName'] = isset($arr['GroupName']) ? $arr['GroupName']: '';
                array_push($final_arr, $data);
            }
        }

        return json_encode($final_arr);
    }

    /**
     * @todo edmart: refactor, make it dynamic and move to infusionsoft service
     *
     * @param $account_id
     * @return mixed
     */
    public function getCustomFields($infusionsoft, $infs_fields, $form_id, $return) {
        $query_fields = array('FormId' =>$form_id);
        $returnFields = array($return);

        $id = \Auth::user()->id;

        try {
            $this->infusionsoft_service->checkIfRefreshTokenHasExpired($id);
            $result = $infusionsoft->data()->query("DataFormField", 1000, 0, $query_fields, $returnFields, 'Id', true);

            if (!$result) {
                $result = [];
            }
        } catch (\Exception $e) {
            return redirect('/csvimport')
                ->with('error', 'Please refresh your token.');
        }

        $final = [];

        // set infs fields data_type and is_custom
        // is_custom : determines if this field is a custom field
        foreach($infs_fields as $field => $data_type) {
            $final[$field]['data_type'] = $data_type;
            $final[$field]['is_custom'] = false;
        }

        if (count($result)) {
            foreach ($result as $res) {
                $fieldname = $res['Name'];
                $final[$fieldname]['data_type'] = 'String';
                $final[$fieldname]['is_custom'] = true;
            }
        }

        ksort($final, SORT_STRING);

        return $final;
    }

    /**
     * @todo edmart: refactor, move to infusionsoft service
     *
     * @param $account_id
     * @return Infusionsoft
     */
    public function getInfusionsoftAccount($account_id) {
        //connect with IS to get custom fields of selected IS account
        if (Auth::user()->role_id == 1) {
            $infusionsoft = new Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        } else {
            $infusionsoft = new Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        }

        $accessToken = InfsAccount::where('id', $account_id)->first();
        $access_token = $accessToken->access_token;

        $token =  new InfusionsoftToken([
            'access_token' => $accessToken->access_token,
            'refresh_token' => $accessToken->referesh_token,
            'expires_in' => Carbon::parse($accessToken->expire_date)->timestamp,
            "token_type" => "bearer",
            "scope"=>"full|".$accessToken->account
        ]);

        \Log::info('Accessing INFS Account...');
        $infusionsoft->setToken($token);

        \Log::info('Set Token... ' . print_r($token, true));
        if ($infusionsoft->getToken()) {
            try {
            } catch (\Infusionsoft\TokenExpiredException $e) {
                $this->infusionsoft_service->handler([$infusionsoft, 'refreshAccessToken']);
            }
        }
        return $infusionsoft;
    }

}