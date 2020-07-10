<?php

namespace App\Helpers;

use App\CsvImportRecord;
use App\InfsCompanyMeta;
use App\InfsProductMeta;
use App\Repositories\CsvImportRecordRepository;
use App\Repositories\CsvImportRepository;
use App\Repositories\InfsCompanyMetaRepository;
use App\Repositories\InfsCompanyRepository;
use App\Repositories\InfsContactMetaRepository;
use App\Repositories\InfsContactRepository;
use App\Repositories\InfsOrderRepository;
use App\Repositories\InfsProductRepository;
use App\Repositories\InfusionsoftAccountRepository;
use App\Repositories\UserRepository;
use Symfony\Component\HttpFoundation\ParameterBag;

class Helpers
{

    const REPO_INFS_CONTACTS = 'infs_contacts';
    const REPO_INFS_COMPANIES = 'infs_companies';
    const REPO_INFS_ORDERS = 'infs_orders';
    const REPO_INFS_PRODUCTS = 'infs_products';
    const REPO_INFS_CONTACTS_META = 'infs_contacts_meta';
    const REPO_INFS_COMPANIES_META = 'infs_companies_meta';
    const REPO_INFS_ORDERS_META = 'infs_orders_meta';
    const REPO_INFS_PRODUCTS_META = 'infs_products_meta';
    const REPO_CSV_IMPORTS = 'csv_imports';
    const REPO_CSV_IMPORT_RECORD = 'csv_import_records';
    const REPO_INFUSIONSOFT_ACCOUNTS = 'infusionsoft_accounts';
    const REPO_USERS = 'users';

    const VALUE_ARR = 'arr';
    const VALUE_OBJ = 'obj';

    const WAIT_MINUTES = 30;

    public function __construct()
    {
    }

    public function isUserGuest()
    {
        return \Auth::guest();
    }

    public function getLoggedInUser()
    {
        return \Auth::user();
    }

    /**
     * Checks if item is null, return value if not
     *
     * @param $item
     * @param $key
     * @param $type either 'obj' or 'arr'
     * @return null
     */
    public static function getValueOrNull($item, $key, $type = self::VALUE_OBJ) {
        return $type == self::VALUE_OBJ
            ? isset($item->{$key}) ? $item->{$key} : null
            : isset($item[$key]) ? $item[$key] : null;
    }

    /**
     * Add item if the key doesn't exist
     *
     * @param $items
     * @param $add
     * @param $key
     * @return array
     */
    public static function addToArrayIfKeyNotExist($items, $add, $key) {
        $result = $items;

        if (!array_key_exists($key, $items)) {
            $result[$key] = $add;
        }

        return $result;
    }

    /**
     * Get the value of the first element
     *
     * @param $mapped
     * @return mixed
     */
    public static function getValueFromMap($mapped) {
        reset($mapped);

        $key = key($mapped);

        $value = $mapped[$key];

        return $value;
    }

    /**
     * Update the values of @array from the values taken from the mapped data
     *
     * @param $array
     * @param $mapped
     * @param $type
     * @return mixed
     */
    public static function storeValuesFromMap($array, $mapped, $type) {
        // store to contact
        $array[$type] = $mapped;

        return $array;
    }

    /**
     * Check for duplicates on the data
     *
     * @param $item
     * @param $csv_import
     * @param $repository
     * @param $type
     * @return mixed
     */
    public static function checkDuplicates($item, $csv_import, $repository, $type) {
        // initialize csv improt repository
        $csv_import_repo = new CsvImportRepository;

        // get matching rules
        $matching_rules = $csv_import_repo->getMatchingRules($csv_import->id, $type);

        // check the matching fields for this type
        if (!isset($matching_rules)) {
            return false;
        }

        // get metadata repository
        $meta_repo = $repository->getMetaRepository();

        // get the item by querying the rules
        $item = $meta_repo->findByMatchingRule($matching_rules, $item);

        return $item;
    }
    
    /**
     * Get the repository based from the map type
     *
     * @param $type
     * @return InfsCompanyRepository|InfsContactRepository|InfsOrderRepository|InfsProductRepository|null
     */
    public static function getRepository($type) {
        $repository = null;

        switch($type) {
            case CsvToInfsMapper::MAP_CONTACT:
                $repository = new InfsContactRepository;
                break;
            case CsvToInfsMapper::MAP_COMPANY:
                $repository = new InfsCompanyRepository;
                break;
            case CsvToInfsMapper::MAP_PRODUCT:
                $repository = new InfsProductRepository;
                break;
            case CsvToInfsMapper::MAP_ORDER:
                $repository = new InfsOrderRepository;
                break;
        }

        return $repository;
    }

    /**
     * Check if item is existing on the meta
     *
     * @param $rules
     * @param $item
     * @param $model
     * @return mixed
     */
    public static function checkMetaDuplicates($rules, $item, $model) {

        // store the meta (if found) here
        $result = null;

        // used to reset the model for each loop
        $temp_model = $model;

        // basically checks each rule and search them if they exist on the meta table
        foreach($rules as $key => $infs_field) {
            $result = null;
            $m = $temp_model;

            $value = $item[$infs_field];

            $m = $m->where('field_name', $infs_field)
                ->where('field_value', $value);

            $result = $m->first();

            // if meta is found, stop the loop
            if (isset($result)) {
                break;
            }
        }

        return $result;
    }

    /**
     * Get the csv data
     *
     * @param $file
     * @return bool|mixed
     */
    public static function getCsvData($file) {
        // check if csv file has the correct format
        $is_csv_allowed = self::checkCsvFormat($file);

        // return false if no csv data
        if (!$is_csv_allowed) return false;

        // get csv path
        $csv_path = realpath($file);

        // get original file name
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // generate file name
        $file_name = time().'_' . $name . '.'. FileHelper::getFileExtension($file);

        // parse the data
        $csv_data = self::parseCsvData($csv_path, $file_name);

        // move file to specified folder
        $destination = storage_path()."/assets/uploads/csv_files";
        self::moveFile($file, $destination);

        return $csv_data;
    }

    /**
     * Returns true if file is allowed
     *
     * @param $file
     * @return bool
     */
    public static function checkCsvFormat($file) {
        // allowed csv formats
        $formats = (new CsvHelper)->getAllowedFormats();

        // get the extension of the csv
        $file_ext = FileHelper::getFileExtension($file);

        return in_array($file_ext, $formats);
    }

    /**
     * Get the data from the CSV
     *
     * @param $full_path
     * @param $filename
     * @return mixed
     */
    public static function parseCsvData($full_path, $filename) {
        // prepare csv data
        $csv_helper = new CSVHelper;
        // set csv data
        $data = $csv_helper->setPath($full_path)
            ->setFileName($filename)
            ->parse();

        return $data;
    }

    /**
     * Move uploaded file to a specified folder
     * @param $file
     */
    public static function moveFile($file, $destination)  {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $upload_file	  = time().'_' . $name . '.'. FileHelper::getFileExtension($file);

        $file->move($destination, $upload_file);
    }

    /**
     * Remove all data with null or 0
     *
     * @param $request
     * @return array
     */
    public static function sanitizeStep2($request) {
        // get settings from the request
        $temp_settings = $request->csv_settings;

        // store the final csv settings in this variable
        $csv_settings = [];

        // loop each setting from the request and create an item per type
        foreach($temp_settings as $type => $value) {
            // if type=company and doesn't have create_company field, skip it
            if ($type == "company" && !isset($request->create_company)) continue;

            // initialize the variable for type
            $csv_settings[$type] = [];

            // loop each type's content and remove the fields which have "0" values
            foreach($value as $key => $field) {
                if ($field == "0" || is_null($field)) continue;

                $csv_settings[$type][$key] = $field;
            }

            // if array is empty, just remove the settings
            if (empty($csv_settings[$type])) unset($csv_settings[$type]);
        }

        return $csv_settings;
    }


    /**
     * Get the fields for infusionsoft
     *
     * @param $type
     * @return array|mixed
     */
    public static function getInfusionsoftConfig($type) {
        if ($type == 'Contact') {
            $config = config('infusionsoft.infusionsoftFields');
        } else {
            $config = config('infusionsoft.companyFields');
        }

        return $config;
    }

    /**
     * Find the value from the matching rules
     *
     * @param $rules
     * @param $key
     * @return false|int|string
     */
    public static function checkIfKeyInMatchingRules($rules, $key) {
        return array_search($key, $rules);
    }

    /**
     * Generate data that are not included on the mapping and matching
     *
     * @param $csv_import_id
     * @param $type
     */
//    public static function generateOtherData($csv_import_id, $type) {
//        $other_data = [];
//
//        if ($type == CsvStatus::INFS_CONTACT) {
//
//            $other_data = self::getCompanyAsOtherData($csv_import_id, $other_data);
//
//        }
//
//        return $other_data;
//    }

    /**
     * During the mapping phase, company is sometimes mapped by the user.
     * In this case, it should check if field name 'CompanyId' is included on the mapped fields.
     * If field name 'CompanyId' is not in the mapped fields,
     * then we should check for field name 'Company'.
     *
     * @param $results
     * @param $csv_import
     * @param $csv_import_record
     * @param $mapped_fields
     * @return mixed
     */
    public static function getCompanyAsAdditionalField($results, $csv_import, $csv_import_record, $mapped_fields) {
        // get csv import repo
        $infs_company_repo = new InfsCompanyRepository;

        // map the related company
        $company_map = CsvToInfsMapper::company($csv_import, $csv_import_record);

        // initialize field name for CompanyId
        $field_name_company_id = 'CompanyID';
        $field_name_company = 'Company';

        // get CompanyId and Company from the map
        $company_id = Helpers::getValueOrNull($company_map, $field_name_company_id, Helpers::VALUE_ARR);
        $company = Helpers::getValueOrNull($company_map, $field_name_company, Helpers::VALUE_ARR);

        // get CompanyId if Company Name exists on db
        if (isset($company)) {
            $company_id = $infs_company_repo->getCompanyId($csv_import->id, $company);
        } else { // take company name from the request
            $company = isset($mapped_fields[$field_name_company]) ? $mapped_fields[$field_name_company] : null;
            $company_id = null;
        }

        // add to meta even if it's null
        $results[$field_name_company] = $company;
        $results[$field_name_company_id] = $company_id;

        return $results;
    }

    /**
     * Add email opt in to the mapper
     *
     * @param $results
     * @param $csv_import
     * @param $csv_import_record
     * @return mixed
     */
    public static function getContactOptInAsAdditionalField($results, $csv_import) {
        // email_opt_in : on
        $csv_import_repo = new CsvImportRepository;

        $opt_in = $csv_import_repo->getOptIn($csv_import->id);

        if ($opt_in['email_opt_in'] == 'on') {
            $results['_OptInReason'] = $opt_in['email_opt_in_reason'];
        }

        return $results;
    }

    /**
     * This assumes that infs company is already created in infusionsoft
     *
     * @param $csv_import_id
     */
//    public static function getCompanyAsOtherData($other_data) {
//        $infs_company_repo = new InfsCompanyRepository;
//        $infs_company_meta_repo = new InfsCompanyRepository;
//
//        // find meta with field_name as 'Company'
//        $field_name = 'Company';
//        $company_meta = $infs_company_meta_repo->findByFieldName($field_name);
//
//        // if company meta exists, find the infs company and get the id
//        if(isset($company_meta)) {
//            // find the infs company
//            $infs_company = $infs_company_repo->find($company_meta->company_id);
//
//            // get id if exists
//            $infs_id = self::getValueOrNull($infs_company, 'company_id');
//
//            // add to other data
//            if (isset($infs_id)) {
//                $other_data['CompanyID'] = $infs_id;
//            }
//        }
//
//        return $other_data;
//    }

    /**
     *
     *
     * @param $meta_repository : the meta repository to search for
     * @param $map  : this is an array taken from the CsvToInfsMapper::map() method
     * @param $field_name : the INFS field name that needs to be searched
     * @return array
     */
//    public static function mapAdditionalFields($meta_repository, $map, $field_name) {
//        $result = [];
//
//        $field_value = Helpers::getValueOrNull($map, $field_name, Helpers::VALUE_ARR);
//
//        if (isset($field_value)) {
//            $meta = $meta_repository->findByFieldNameAndValue($field_name, $field_value);
//
//            if ($meta) {
//                $result[$field_name] = $field_value;
//            }
//        }
//
//        return $result;
//    }

    /**
     * Some fields are null and needs to be filled in
     *
     * @param $meta_items
     */
    public static function processNullFields($meta_items) {
        // get all items with null value
        $null_items = $meta_items->reject(function ($value, $key) {
            return isset($value->field_value);
        })->all();

        foreach($null_items as $key => $item) {
            // check for CompanyId
            if ($item->field_name == 'CompanyID') {
                self::updateCompanyIdFromMeta($item);
            }
        }


    }

    /**
     * Updates the CompanyID field on the InfsContactMeta.
     * This happens because the Company is not yet created in infusionsoft during the mapping phase.
     *
     * @param $infs_item
     * @return null
     */
    public static function updateCompanyIdFromMeta($infs_item) {
        $result = null;
        $infs_company_repo = new InfsCompanyRepository;
        $infs_company_meta_repo = new InfsCompanyMetaRepository;
        $infs_contact_meta_repo = new InfsContactMetaRepository;

        // set field name to CompanyId
        $field_name = 'Company';

        // get CompanyId from this infs_item
        $contact_company_meta = $infs_contact_meta_repo->getFieldNameAndByMainId($infs_item->contact_id, $field_name)->first();

        // if company meta exists on the contact, apply the contact id
        if (isset($contact_company_meta)) {
            // get the company meta
            $company_meta = $infs_company_meta_repo->findByFieldNameAndValue($field_name, $contact_company_meta->field_value);

            // skip if company meta is null
            if (!isset($company_meta->field_value)) return $result;

            // get the infs company
            $infs_company = $infs_company_repo->find($company_meta->company_id);

            // prepare data to use
            $parameters = new ParameterBag;
            $parameters->set('field_value', $infs_company->company_id);

            $infs_contact_meta_repo->update($infs_item->id, $parameters->all());

            // $result = $infs_contact_meta_repo->find($infs_item->id);
        }

        return $result;
    }

    /**
     * Get the matching field obj based from the given infs type
     *
     * @param $type
     * @return string|null
     */
    public static function getMatchingFieldByInfs($type) {
        $matching_field_obj = null;

        switch($type) {
            case CsvStatus::INFS_CONTACT:
                $matching_field_obj = CsvStatus::TYPE_CONTACT;
                break;
            case CsvStatus::INFS_COMPANY:
                $matching_field_obj = CsvStatus::TYPE_COMPANY;
                break;
        }

        return $matching_field_obj;
    }

    /**
     * Check if infs field is custom field
     *
     * @param $type
     * @param $infs_field
     * @return bool
     */
    public static function isCustomField($type, $infs_field) {
        $result = false;
        $config = null;
        switch($type) {
            case CsvToInfsMapper::MAP_CONTACT:
                $config = config('infusionsoft.infusionsoftFields');
                break;
            case CsvToInfsMapper::MAP_COMPANY:
                $config = config('infusionsoft.companyFields');
                break;
            case CsvToInfsMapper::MAP_ORDER:
                $config = config('infusionsoft.ordersFields');
                break;
            case CsvToInfsMapper::MAP_PRODUCT:
                $config = config('infusionsoft.productInfsFields');
                break;
        }

        if (isset($config)) {
            $result = !array_has($config, $infs_field);
        }

        return $result;
    }

    /**
     * Get the field/match fields from csv import record based from the type of infs object
     *
     * @param $type
     * @return null
     */
    public static function getImportRecordInfsMatchField($type) {
        $result = null;
        $csv_import_record_repo = new CsvImportRecordRepository;

        if ($type == CsvStatus::INFS_COMPANY ) {
            $result['field'] = $csv_import_record_repo->getInfsCompanyField();
            $result['match'] = $csv_import_record_repo->getInfsCompanyFieldMatched();
        } elseif ($type == CsvStatus::INFS_CONTACT) {
            $result['field'] = $csv_import_record_repo->getInfsContactField();
            $result['match'] = $csv_import_record_repo->getInfsContactFieldMatched();
        }

        return $result;
    }

    /**
     * Get the number of minutes the csv_import will be synced to infs
     *
     * @param $csv_import_id
     * @return float|int
     */
    public static function getWaitMinutes($csv_import_id) {
        $csv_import_repo = new CsvImportRepository;

        $csv_import_records = $csv_import_repo->getRecords($csv_import_id)->get();

        // assuming records have 2 seconds for each infs sync
        $wait_seconds = count($csv_import_records) * 2;

        // calculate minutes
        $wait_minutes = intval(($wait_seconds / 60));

        // add minutes depending on $wait_minutes
        // if $wait_minutes is less than 30, default is 30minutes
        // else, add 3 minutes offset
         $wait_minutes = $wait_minutes < self::WAIT_MINUTES
             ? self::WAIT_MINUTES
             : $wait_minutes + 3;

        return $wait_minutes;
    }

    /**
     * Send email whenever there are failed infs syncs
     *
     * @param $csv_import_id
     */
    public static function sendInfsFailedEmail($csv_import_id) {
        // initialize repositories
        $infs_account_repo = new InfusionsoftAccountRepository;
        $user_repo = new UserRepository;
        $csv_import_repo = new CsvImportRepository;

        // set variables
        $csv_import = $csv_import_repo->find($csv_import_id);
        $user = $user_repo->find($csv_import->user_id);
        $infs_account = $infs_account_repo->getUser($user->id)->first();

        $content = 'Your infusionsoft token failed to refresh for account ' . $infs_account->name;

        $res = \Mail::send('emails.tokenReauth', ['name' => '', 'content' => $content], function ($message) use ($user) {
            $message->from(config('emails.help'), 'FusedTools');
            $message->to($user->email, '')->subject('Renew Infusionsoft Token');
        });

        return $res;
    }


    public static function toastr($message, $alert_type) {
        return array(
            'message' => $message,
            'alert-type' => $alert_type
        );
    }

}
