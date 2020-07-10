<?php
namespace App\Http\Controllers;

use URL;
use App\Helpers\CsvHelper;
use App\Helpers\CsvStatus;
use App\Repositories\CsvImportRecordRepository;
use App\Repositories\CsvImportRepository;
use Illuminate\Http\Request;

use App\Jobs\ImportCSVJob;
use App\Services\InfusionSoftService;
use App\Http\Requests;
use Auth;
use App\InfsAccount;
use Excel;
use App\CsvImports;
use Infusionsoft;
use App\CsvImportsRecs;
use Illuminate\Support\Facades\DB;
use Infusionsoft\Token as InfusionsoftToken;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\Session;

class ImportCSVController extends Controller
{
    private $authUser = array();
    protected $infusionSoftService;
    
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        $this->middleware('auth');
        // $this->authUser = Auth::user();

        $this->infusionSoftService = $infusionSoftService;
    }
    
    /**
    * Show the csvimports record and a button to add new
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        $imports = CsvImports::where('user_id', Auth::id())
             ->orderBy('status', 'desc')
             ->orderBy('created_at', 'desc')
             ->orderBy('import_title', 'desc')->get();

        return view('importCSV.index', compact('imports'));
    }
    
    /**
    * Show the csvimports step 1, asking for infs app, import name and csv file
    *
    * @return \Illuminate\Http\Response
    */
    public function importStep1(Request $request)
    {

        $userID	= Auth::id();
        $parm = $request->parm;
        if (empty($parm)) {
            $request->session()->forget('CSV_import');
        }

        if (isset($request->parm) && $request->parm != 'back') {
            $import = CsvImports::where('user_id', $userID)
                ->where([
                    [ 'id', '=', $parm ],
                    [ 'status', '=', 0 ]
                ])->first();

            if (!$import) {
                return redirect('/csvimport')
                     ->with('error', 'Import not found.');
            }

            $data = [
                'account_id' => $import->account_id,
                'import_title' => $import->import_title,
                'csv_import_id' => $import->id,
                'IS_fields' => $import->id,
                'order_fields' => $import->id,
                'csv_fields' => $import->import_results,
                'fields_arr' => $import->import_results,
                'settings' => $import->csv_settings,
                'csv_file' => $import->csv_file,
            ];

            
            $request->session()->put('CSV_import', $data);
        }
        $infusionsoftAccounts = InfsAccount::where([
            [ 'user_id', '=', $userID ],
            [ 'expire_date', '>=', Carbon::now()]
        ])->get();
        
        return view('importCSV.step1', compact('infusionsoftAccounts'));
    }
    
    /**
    * Show the csvimports step 2 - matching fields
    *
    * @return \Illuminate\Http\Response
    */
    public function importStep2(Request $request)
    {
        $csv_import_data = $request->session()->get('CSV_import');
        $userID = Auth::id();
        $create = array();
        $csv_full = null;

        if ($request->isMethod('post') && empty($request->parm)) {
            $this->validate($request, [
                "import_title" => "required",
            ]);
            
            $file = $request->file('csv_file');
            $file_ext = strtolower($file->getClientOriginalExtension());
            
            if (!in_array($file_ext, array('csv','xls','xlsx'))) {
                return back()->with('error', 'Please upload valid csv format.');
            }

            // get data from csv
            $csv_path = realpath($request->file('csv_file'));
            $csv_data = $this->getCsvData($csv_path, $request->file('csv_file'));

            //do validate extension dont forget
            //upload and move file
            $name = pathinfo($request->file('csv_file')->getClientOriginalName(), PATHINFO_FILENAME);
            $destination_path = public_path()."/assets/uploads/csv_files";
            $upload_file	  = time().'_' . $name . '.'.$file_ext;
            $file->move($destination_path, $upload_file);
            
            $create['csv_file'] = $upload_file;
            $csv_import_data['csv_file'] = $upload_file;
        }
        
        $this->validate($request, [
            "account" => "required",
            "import_title" => "required",
        ]);

            
        $account_id 		= $request->account;
        
        $create['user_id'] = $userID;
        $create['import_title'] = $request->import_title;
        $create['started_at'] = Carbon::now();
        
        $create['account_id'] = $account_id;
        
        if (isset($csv_import_data['csv_import_id']) && !empty($csv_import_data['csv_import_id'])) {
            $csv_import = CsvImports::where('id', $csv_import_data['csv_import_id'])->update($create);
            $csv_import_id = $csv_import_data['csv_import_id'];
        } else {
            $csv_import = CsvImports::create($create);
            $csv_import_id = $csv_import->id;

            $this->storeCsvImportRecords($csv_data, $csv_import_id);
        }
        
        //set fields in session
        $csv_import_data['account_id'] = $account_id;
        $csv_import_data['import_title'] = $request->import_title;
        
        $csv_import_data['csv_import_id'] = $csv_import_id;
        
        $request->session()->put('CSV_import', $csv_import_data);
        
        
        $CSV_import = $request->session()->get('CSV_import');
        
        //read csv/excel file to get field names
        $file_fields_arr = '';
        $result = Excel::load("assets/uploads/csv_files/".$CSV_import['csv_file'])->first();

        if (!empty($result)) {
            $file_fields_arr = array_keys($result->toArray());
        } else {
            return back()->with('error', 'Uploded file is empty.');
        }
        
        $csv_import_data = $request->session()->get('CSV_import');
        
        $infusionsoft = $this->infusionSoftAccount($csv_import_data['account_id']);
        
        $IS_fields 	      = config('infusionsoft.infusionsoftFields');
        $query_fields     = array('FormId' =>'-1');
        $returnFields     = array('Name');
        

        $result           = $infusionsoft->data()->query("DataFormField", 1000, 0, $query_fields, $returnFields, 'Id', true);

        if (count($result)) {
            foreach ($result as $res) {
                $fieldname = $res['Name'];
                $IS_fields[$fieldname] = 'String';
            }
        }
        ksort($IS_fields, SORT_STRING);
        
        $order_fields 	    = config('infusionsoft.ordersFields');
        $orderResult        = $infusionsoft->data()->query("DataFormField", 1000, 0, array('FormId' =>'-9'), array('Name'), 'Id', true);

        if (count($orderResult)) {
            foreach ($orderResult as $res) {
                $fieldname = $res['Name'];
                $order_fields[$fieldname] = 'String';
            }
        }
        ksort($order_fields, SORT_STRING);
        
        if ($request->session()->get('CSV_import')) {
            $userImports = CsvImports::select('id', 'import_title')->where('user_id', $userID)->whereNotIn('id', [$CSV_import['csv_import_id']])->where('import_title', '!=', '')->get();
            $csv_import_data['IS_fields']  = $IS_fields;
            $csv_import_data['order_fields']  = $order_fields;
            $csv_import_data['csv_fields']  = $file_fields_arr;
            $request->session()->put('CSV_import', $csv_import_data);
            
            return view('importCSV.step2', compact('file_fields_arr', 'IS_fields', 'order_fields', 'userImports'));
        } else {
            return redirect('/csvimport/step1');
        }
    }
    
    public function applySettings(Request $request)
    {
        $params = $request->all();
        $userImport = CsvImports::where('user_id', Auth::id())->where('id', $params['import_account'])->first();
        if (empty($userImport)) {
            return array('status' => 'failed', 'message' => 'Import settings not found.');
        }
        
        $csv_import_data = $request->session()->get('CSV_import');
        
        $import_settings = array();
        
        $csv_import_data['fields_arr'] = $userImport->import_results;
        $csv_import_data['settings'] = json_decode($userImport->csv_settings, true);
        
        $request->session()->put('CSV_import', $csv_import_data);
        
        return array('status' => 'success', 'message' => 'Import settings applied. Going to reload page');
    }
    
    public function importStep3(Request $request)
    {
        if (!$request->session()->get('CSV_import')) {
            return redirect('/csvimport/step1')->with('error', 'Error- Session has expired.');
        }
        
        $csv_fields 		 = $request->csv_fields;
        $infusionsoft_fields = $request->infusionsoft_fields;
        
        $comp_infs_fields = array();
        $order_infs_fields = array();
        
        if (isset($request->company_creation) && $request->company_creation == 'yes') {
            $company_fields 	 = $request->company_fields;
            $comp_infs_fields 	 = $request->comp_infusionsoft_fields;
        }
        if (isset($request->order_creation) && $request->order_creation == 'yes') {
            $order_fields 		 = $request->order_fields;
            $order_infs_fields   = $request->order_infusionsoft_fields;
        }

        $userID				= Auth::id();
        $csv_import_data	= $request->session()->get('CSV_import');
        $fields_arr			= array();
        
        if ($infusionsoft_fields) {
            foreach ($infusionsoft_fields as $key => $field) {
                if (!empty($field)) {
                    $fields_arr['contacts'][$csv_fields[$key]] = $field;
                }
            }
            if ($comp_infs_fields) {
                foreach ($comp_infs_fields as $key => $field) {
                    if (!empty($field)) {
                        $fields_arr['company'][$company_fields[$key]] = $field;
                    }
                }
            }
            if ($order_infs_fields) {
                foreach ($order_infs_fields as $key => $field) {
                    if (!empty($field)) {
                        $fields_arr['orders'][$order_fields[$key]] = $field;
                    }
                }
            }

            $update				= array(
                    'import_results'    => json_encode($fields_arr)
                );
            
            if (isset($csv_import_data['csv_import_id']) && !empty($csv_import_data['csv_import_id'])) {
                $csv_import 		= CsvImports::where('id', $csv_import_data['csv_import_id'])->update($update);
                $csv_import_id      = $csv_import_data['csv_import_id'];
            }
            if (!$csv_import) {
                return back()->with('error', 'Error occurs while completing the request. Please try after sometime');
            }
        
            //read csv/excel file to upload  data to UsersImportsdata table
            // $csv_file_data = Excel::load("assets/uploads/csv_files/".$csv_import_data['csv_file'])->get()->toArray();

            // create infs local records here

            $csv_import_data['csv_import_id']  = $csv_import_id;
            $csv_import_data['fields_arr'] 		= $fields_arr;
            $request->session()->put('CSV_import', $csv_import_data);
        }
        return view('importCSV.step3');
    }
    
    public function importStep4(Request $request)
    {
        if (!$request->session()->get('CSV_import.csv_import_id')) {
            return redirect('/csvimport/step1')
                 ->with('error', 'Error- Session has expired.');
        }
        
        $params = $request->all();
        $settings = array();

        if (isset($params['filter_contact']) && $request['filter_contact']) {
            if ($params['filter_contact'] != 'create') {
                foreach ($params['match_contact_csv'] as $key => $csvfield) {
                    $settings['contacts']['fields'][$csvfield] = $params['match_contact_infs'][$key];
                }
            }
            $settings['contacts']['type'] = $params['filter_contact'];
        }
        
        if (isset($params['filter_company']) && !empty($params['filter_company'])) {
            if ($params['filter_company'] != 'create') {
                foreach ($params['match_company_csv'] as $key => $csvfield) {
                    $settings['company']['fields'][$csvfield] = $params['match_company_infs'][$key];
                }
            }
            $settings['company']['type'] = $params['filter_company'];
        }
        
        if (isset($params['filter_order']) && !empty($params['filter_order'])) {
            if ($params['filter_order'] != 'create') {
                foreach ($params['match_order_csv'] as $key => $csvfield) {
                    $settings['orders']['fields'][$csvfield] = $params['match_order_infs'][$key];
                }
            }
            $settings['orders']['type'] = $params['filter_order'];
        }
        
        if (isset($params['filter_product']) && !empty($params['filter_product'])) {
            if ($params['filter_product'] != 'create') {
                foreach ($params['match_product_csv'] as $key => $csvfield) {
                    $settings['products']['fields'][$csvfield] = $params['match_product_infs'][$key];
                }
            }
            $settings['products']['type'] = $params['filter_product'];
        }
        
        if (isset($params['order_split']) && !empty($params['order_split'])) {
            if ($params['order_split'] == 'custom') {
                $settings['orders']['settings']['split'] = [
                    'sku' => $params['sku_delimiter'],
                    'name' => $params['name_delimiter'],
                    'qty' => $params['qty_delimiter'],
                    'price' => $params['price_delimiter']
                ];
            } else {
                $settings['orders']['settings']['order_id'] = $params['order_id'];
            }
            $settings['orders']['settings']['splitType'] = $params['order_split'];
        }
        
        $csv_import_data = $request->session()->get('CSV_import');
        
        $csv_import_id = $csv_import_data['csv_import_id'];
        $csv_import_data['settings'] = $settings;
        $request->session()->put('CSV_import', $csv_import_data);
        
        $account_id = $request->session()->get('CSV_import.account_id');
        $infusionsoft = $this->infusionSoftAccount($account_id);
        
        $query_fields = array('GroupName' =>'%');
        $returnFields = array('Id','GroupName');
        $tags_arr = $infusionsoft->data()->query("ContactGroup", 1000, 0, $query_fields, $returnFields, 'Id', true);

        $final_arr = array();
        if (count($tags_arr)) {
            foreach ($tags_arr as $arr) {
                $data['id'] = $arr['Id'];
                $data['GroupName'] = isset($arr['GroupName']) ? $arr['GroupName']: '';
                array_push($final_arr, $data);
            }
        }
        $tags = json_encode($final_arr);
        
        return view('importCSV.step4', compact('tags'));
    }
     
    public function importStep5(Request $request)
    {
        if (!$request->session()->get('CSV_import.csv_import_id')) {
            return redirect('/csvimport/step1')
            ->with('error', 'Error- Session has expired.');
        }
        
        $userID = Auth::id();
        $CSV_import = $request->session()->get('CSV_import');
        
        $selected_tags = [];
        $settings = $CSV_import['settings'];
        
        if ($request->apply_tags == "yes") {
            $posted_tags = $request->tags;
            $tags_array = explode(",", $posted_tags);
            $tags = ['tags' => $tags_array];
            $settings = array_merge($CSV_import['settings'], $tags);
        }
        
        $csvImport = CsvImports::find($CSV_import['csv_import_id']);
        $csvImport->update([ 
            'csv_settings' => json_encode($settings),
            'status' => 0
        ]);
        
        $infsAccount = InfsAccount::where('id', $CSV_import['account_id'])->first();
        $request->session()->forget('CSV_import');
        
        $view_data = [
            'message' => 'Import In Progress - We WIll Notify You When If Is Complete.',
            'account' => $infsAccount->account
        ];

        // mark csv import as import finished
        $csv_import_repo = new CsvImportRepository;
        $csv_import_repo->markAsImportFinished($csvImport->id);

        return view('importCSV.step5', $view_data);
    }
    
    public function deleteImport($importId)
    {
        $userID	= Auth::id();
        $import = CsvImports::where('user_id', $userID)->where('id', $importId)->where('status', 0)->first();
        if (empty($import)) {
            return array('status' => 'failed', 'message' => 'Sorry you can\'t delete this import.');
        }
        CsvImports::where('id', $import->id)->delete();
        return array('status' => 'success', 'message' => 'Import deleted successfully.');
    }
    
    public function cancelImport($importId)
    {
        $userID	= Auth::id();
        $import = CsvImports::where('user_id', $userID)->where('id', $importId)->where('status', 1)->first();
        if (empty($import)) {
            return array('status' => 'failed', 'message' => 'You can not cancel this import.');
        }
        CsvImports::where('id', $import->id)->update(['status'=>0]);
        return array('status' => 'success', 'message' => 'Import canceled successfully.');
    }
    
    public function infusionSoftAccount($accountId)
    {
        $user = Auth::user();
        //connect with IS to get custom fields of selected IS account
        if ($user->role_id == 1) {
            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_ADMIN_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_ADMIN_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        } else {
            $infusionsoft = new Infusionsoft\Infusionsoft(array(
                'clientId'     => env('INFUSIONSOFT_CLIENT_ID'),
                'clientSecret' => env('INFUSIONSOFT_CLIENT_SECRET'),
                'redirectUri'  => URL::to('/').env('INFUSIONSOFT_REDIRECT_URI'),
            ));
        }
    
        $accessToken = InfsAccount::where('id', $accountId)->first();
        $access_token      = $accessToken->access_token;
        
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
                $this->infusionSoftService->handler([$infusionsoft, 'refreshAccessToken']);
            }
        }
        return $infusionsoft;
    }

    /**
     * Get the data from the CSV
     *
     * @param $full_path
     * @param $filename
     * @return mixed
     */
    public function getCsvData($full_path, $filename) {
        // prepare csv data
        $csv_helper = new CsvHelper;

        // set csv data
        $data = $csv_helper->setPath($full_path)
            ->setFileName($filename)
            ->parse()
            ->getData();

        return $data;
    }

    /**
     * Save rows from the csv one by one
     *
     * @param $csv_data
     * @param $csv_import_id
     */
    public function storeCsvImportRecords($csv_data, $csv_import_id) {
        $csv_import_record_repo = new CsvImportRecordRepository;
        foreach($csv_data as $key => $value) {
            $params = new ParameterBag;

            $params->set('csv_import_id', $csv_import_id);
            $params->set('data', json_encode($value));
            $params->set('status', CsvStatus::CSV_RECORD_PENDING);

            $csv_import_record_repo->store($params);
        }
    }
}
