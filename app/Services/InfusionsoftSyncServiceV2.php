<?php

namespace App\Services;

use App\CsvImportRecord;
use App\CsvImports;
use App\Helpers\CsvStatus;
use App\Helpers\CsvToInfsMapper;
use App\Helpers\Helpers;
use App\Repositories\CsvImportRecordRepository;
use App\Repositories\CsvImportRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\ParameterBag;

class InfusionsoftSyncServiceV2 {

    protected $infusionsoftService;

    protected $csv_import_repo;

    protected $csv_import_record_repo;

    protected $stored_match_data;

    public function __construct(InfusionsoftService $infusionsoftService) {
        $this->infusionsoftService = $infusionsoftService;
        $this->csv_import_repo = new CsvImportRepository;
        $this->csv_import_record_repo = new CsvImportRecordRepository;
        $this->stored_match_data = [];
    }

    public function test() {

        $csv_import = $this->csv_import_repo->find(236);
        $res = $this->getAllInfsItems($csv_import);

        dd($res);
    }

    /**
     * Entry point for syncing items to Infusionsoft
     *
     * @return bool
     */
    public function sync() {
        Log::info('Starting INFS sync...');

        // check if there's a running infs sync
        $csv_import_running = $this->getInfsRunningSync();

        // skip if there are running syncs
        if ($csv_import_running instanceof CsvImports) {
            Log::info('Skipping INFS sync - ongoing sync csv_import id - ' . $csv_import_running->id);
            return false;
        }

        // get first pending csv import
        $csv_import = $this->getImportFinished();

        if ($csv_import instanceof CsvImports) {
            Log::info('Processing import csv_import - ' . $csv_import->id);

            // mark as running infs sync
            $this->csv_import_repo->markAsInfsRunningSync($csv_import->id);

            // loop pending records
            $this->processRecords($csv_import);

            // mark csv import as finished infs sync
            $this->csv_import_repo->markAsInfsFinishedSync($csv_import->id);
        }

        Log::info('INFS sync finished.');
    }

    /**
     * Loop each csv record and process it to sync in Infusionsoft
     *
     * @param $csv_import
     */
    public function processRecords($csv_import) {
        $this->stored_match_data = $this->getAllInfsItems($csv_import);

        // get all CSV_RECORD_PENDING records
        $csv_import_records = $this->csv_import_record_repo->pending($csv_import->id)->get();

        // process each record
        foreach($csv_import_records as $csv_import_record) {
            Log::info('Processing import csv_import_record - ' . $csv_import_record->id);
            // check if import has failed
            $import = $this->csv_import_repo->find($csv_import->id);

            if ($import->status == CsvStatus::INFS_FAILED_SYNC) {
                Log::info('Skipping other records since csv_import has failed - ' . $csv_import->id);
                break;
            }

            // mark csv import record as INFS RUNNING SYNC
            $this->csv_import_record_repo->markAsCsvRecordInfsRunningSync($csv_import_record->id);

            // start syncing records to Infusionsoft
            $this->syncRecord($csv_import_record);

            // get latest csv import record
            $new_csv_import_record = $this->csv_import_record_repo->find($csv_import_record->id);

            // if the record has no errors, mark csv import record as INFS FINISHED SYNC
            if ($new_csv_import_record->status !== CsvStatus::CSV_RECORD_INFS_FAILED_SYNC) {
                $this->csv_import_record_repo->markAsCsvRecordInfsFinishedSync($csv_import_record->id);
            }
        }
    }

    /**
     * @param $csv_import
     * @return array
     */
    public function getAllInfsItems($csv_import) {
        // initialize results
        $results = [];

        // get companies
        $results[CsvStatus::INFS_COMPANY] = $this->getAndTransformInfsItems($csv_import, CsvStatus::INFS_COMPANY, CsvStatus::TYPE_COMPANY);

        // get contacts
        $results[CsvStatus::INFS_CONTACT] = $this->getAndTransformInfsItems($csv_import, CsvStatus::INFS_CONTACT, CsvStatus::TYPE_CONTACT );

        return $results;
    }

    /**
     * Gets all the Infusionsoft items based on the type.
     * Transforms the Infusionsoft Api response and assign them to each matching fields.
     *
     * @param $csv_import
     * @param $type
     * @param $obj_type
     */
    public function getAndTransformInfsItems($csv_import, $type, $obj_type) {
        // get infs items
        $items = $this->getInfsItems($csv_import, $type, $obj_type);

        // transform items
        $transformed_items = $this->transformInfsItem($items, $csv_import, $type, $obj_type);

        return $transformed_items;
    }

    /**
     * Gets all the items from Infusionsoft based on the type given
     *
     * @param $csv_import
     * @param $type
     * @param $obj_type
     * @return array
     */
    public function getInfsItems($csv_import, $type, $obj_type) {
        // initialize
        $return_fields = [];

        // get user id
        $user_id = $csv_import->user_id;

        // get matching fields
        $matching_fields = $this->csv_import_repo->getMatchingRules($csv_import->id, $type);

        // if obj_type doesn't exist on the matching fields,
        // e.g, company was not selected during matching
        // apply a default return field
        if (!array_has($matching_fields, $obj_type)) {
            if ($type === CsvStatus::INFS_COMPANY) {
                $return_fields = ['Company'];
            }
        } else {
            $return_fields = $matching_fields[$obj_type];
        }

        // merge matching_fields and Id to return these fields on the INFS api response
        array_push($return_fields, 'Id');

        // get infs items
        // $res = $this->infusionsoftService->getContacts($user_id, null, "", $return_fields);
        $res = $this->infusionsoftService->search($user_id, $type, ["Id" => "%%"], $return_fields);

        return $res;
    }

    /**
     * Transforms items taken from Infusionsoft API
     *
     * @param $items
     * @param $csv_import
     * @param $type
     * @param $obj_type
     */
    public function transformInfsItem($items, $csv_import, $type, $obj_type) {
        // initialize variables
        $results = [];

        // get matching fields
        $matching_fields = $this->csv_import_repo->getMatchingRules($csv_import->id, $type);

        // if obj_type doesn't exist on the matching fields,
        // e.g, company was not selected during matching
        // apply a default field
        if (!array_has($matching_fields, $obj_type)) {
            if ($type === CsvStatus::INFS_COMPANY) {
                $matching_fields[$obj_type] = ['Company'];
            }
        }

        // transforms the results from INFS
        foreach($matching_fields[$obj_type] as $match_field) {
            $results[$match_field] = [];

            // loop each infs item
            foreach($items as $item) {
                // get the item using the match_field
                $res = isset($item[$match_field]) ? $item[$match_field] : null;

                // only set item if it's not null
                if (isset($res)) {
                    $results[$match_field][strtolower($res)] = $item['Id'];
                }

                $res = null;
            }

        }

        return $results;
    }


    /**
     * Gets all the filters for this specific import and pass it to be processed.
     * Filters are used to determine how we sync to infusionsoft (create only, update only, or both)
     *
     * @param $csv_import_record
     */
    public function syncRecord($csv_import_record) {
        // initialize infs id
        $id = null;

        // get csv import
        $csv_import = $this->csv_import_repo->find($csv_import_record->csv_import_id);

        // set user id
        $user_id = $csv_import->user_id;

        // get filters
        $filters = $this->csv_import_repo->getFilters($csv_import_record->csv_import_id);

        $types = [
            CsvStatus::INFS_COMPANY => CsvStatus::TYPE_COMPANY,
            CsvStatus::INFS_CONTACT => CsvStatus::TYPE_CONTACT,
        ];

        // loop each type
        foreach($types as $type => $obj) {
            // filter is used for checking if it is an update, create or both
            $filter = Helpers::getValueOrNull($filters, $obj, Helpers::VALUE_ARR);

            // get mapped csv_row data
            $csv_row = CsvToInfsMapper::mapByType($type, $csv_import, $csv_import_record);

            // get match fields
            $matching_fields = $this->csv_import_repo->getMatchingRules($csv_import->id, $type);

            // initialize options
            $options['csv_import_record'] = $csv_import_record;
            $options['matching_fields'] = $matching_fields;
            $options['csv_import'] = $csv_import;
            $options['raw_data'] = $csv_import_record->data;
            $options['user_id'] = $user_id;
            $options['data'] = $csv_row;
            $options['type'] = $type;

            // get match fields by obj type (contacts, company, etc)
            $match_fields = Helpers::getValueOrNull($matching_fields, $obj, Helpers::VALUE_ARR);


            // skip matching if filter create only
            if ($filter !== CsvImports::FILTER_CREATE) {
                // skip if match_fields doesn't exist.
                // it means it was not selected on the matching step
                if (!isset($match_fields)) continue;

                foreach($matching_fields[$obj] as $match_field) {
                    //if we retrieve a field for a contact from infusionsoft,
                    // and that field is not set, it won’t return the field.
                    // Ie. If we look for _HouseSize and it’s not set on that
                    // contact it wont return that as a key/value pair.

                    $csv_row_value = Helpers::getValueOrNull($csv_row, $match_field, Helpers::VALUE_ARR);

                    if(isset($csv_row_value)) {

                        $csv_row_value = strtolower($csv_row_value);

                        if (isset($this->stored_match_data[$type][$match_field][$csv_row_value]) ) {

                            $id = $this->stored_match_data[$type][$match_field][$csv_row_value];

                            // Update existing contact
                            // either direct call to infs or write to import_csv_records.infs_contact
                            // and process the the infusionsoft calls last.

                            if ($filter === CsvImports::FILTER_BOTH || $filter == CsvImports::FILTER_UPDATE) {
                                $this->updateInfsItem($id, $type, $csv_row, $user_id, $options);
                            }
                        }
                    }
                }
            }

            // if id is null, item is not yet created on Infusionsoft, so create it
            if(!isset($id)) {
                if ($filter === CsvImports::FILTER_BOTH || $filter == CsvImports::FILTER_CREATE) {
                    // create them and set the values
                    $this->createInfsItem($type, $csv_row, $user_id, $options);
                }
            } else {
                Log::info('Item exists on infusionsoft : ' . $type . ' id - ' . $id);
            }

            $id = null;

        }
    }

    /**
     * Create items in Infusionsoft
     *
     * @param $type
     * @param $data
     * @param $userId
     * @param array $options
     */
    public function createInfsItem($type, $data, $userId, $options = []) {
        try {
            $id = $this->infusionsoftService->create($data, $type, $userId);

            $this->updateStoredMatchingData($id, $data, $type, $options);

            $this->additionalProcessing($id, $options);

            $this->updateCsvImportRecord($id, $options['csv_import_record'], $type,CsvImportRecord::CREATED);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            $this->markFailed($options);
        }
    }

    /**
     * Update items in Infusionsoft
     *
     * @param $id
     * @param $type
     * @param $data
     * @param $userId
     * @param array $options
     */
    public function updateInfsItem($id, $type, $data, $userId, $options = []) {
        try {
            $id = $this->infusionsoftService->update($id, $data, $type, $userId);

            $this->additionalProcessing($id, $options);

            $this->updateCsvImportRecord($id, $options['csv_import_record'], $type,CsvImportRecord::MATCHED);

        } catch (\Exception $e) {
            Log::error($e->getMessage());

            $this->markFailed($options);
        }
    }

    /**
     * Get the first running infs sync
     *
     * @return mixed
     */
    public function getInfsRunningSync() {
        $infs_running_sync = $this->csv_import_repo->infsRunningSync()->first();

        return $infs_running_sync;
    }

    /**
     * Get the first csv_import with IMPORT_FINISHED status
     *
     * @return mixed
     */
    public function getImportFinished() {
        $csv_import = $this->csv_import_repo->importFinished()->orderBy('id', 'asc')->first();

        return $csv_import;
    }

    /**
     * Apply any additional process after updating or creating an infs item
     *
     * @param $id
     * @param $options
     */
    public function additionalProcessing($id, $options) {
        // get type
        $type = $options['type'];

        // additional items to add per type
        switch($type) {
            case CsvStatus::INFS_CONTACT:
                $this->contactAdditionalProcessing($id, $options);
                break;
            case CsvStatus::INFS_COMPANY:
                // add any processing here, if there's any
                break;
        }

    }

    /**
     * Additional processing for the contacts.
     *
     * @param $id
     * @param $options
     */
    public function contactAdditionalProcessing($id, $options) {
        // initialize variables
        $type = $options['type'];
        $data = $options['data'];

        // optin
        $this->optInContact($data);

        // tags
        $this->tagContacts($id, $options);

        // link companies
        $this->linkContactCompanies($id, $options);
    }

    /**
     * Opt in contact
     *
     * @param $data
     */
    public function optInContact($data) {
        $email = Helpers::getValueOrNull($data, 'Email', Helpers::VALUE_ARR);
        $opt_in_reason = Helpers::getValueOrNull($data, '_OptInReason', Helpers::VALUE_ARR);

        if (isset($email) && isset($opt_in_reason)) {
            $this->infusionsoftService->optIn($email, $opt_in_reason);
        }
    }

    /**
     * Tag contacts
     *
     * @param $id
     * @param $options
     */
    public function tagContacts($id, $options) {
        // initialize variables
        $type = $options['type'];
        $csv_import = $options['csv_import'];

        // get tags from the csv import
        $tags = $this->csv_import_repo->getTags($csv_import->id);

        // apply tags if tags is not empty or null
        if (isset($tags) && !empty($tags)) {
            $this->infusionsoftService->tag($id, $tags, $type);
        }
    }

    /**
     * Link company to contact
     *
     * @param $id
     * @param $options
     */
    public function linkContactCompanies($id, $options) {
        Log::info('Linking Company to Contact - id - ' . $id);

        // initialize variables
        $csv_import = Helpers::getValueOrNull($options, 'csv_import', Helpers::VALUE_ARR);
        $csv_import_record = Helpers::getValueOrNull($options, 'csv_import_record', Helpers::VALUE_ARR);
        $data = Helpers::getValueOrNull($options, 'data', Helpers::VALUE_ARR);
        $type = Helpers::getValueOrNull($options, 'type', Helpers::VALUE_ARR);
        $user_id = Helpers::getValueOrNull($options, 'user_id', Helpers::VALUE_ARR);

        // map company
        $company_map = CsvToInfsMapper::mapByType('Company', $csv_import, $csv_import_record);

        // get company or companyId field
        $company = Helpers::getValueOrNull($company_map, 'Company', Helpers::VALUE_ARR);
        $company_id = Helpers::getValueOrNull($company_map, 'CompanyID', Helpers::VALUE_ARR);

        Log::info("Company - " . $company);
        Log::info("CompanyID - " . $company_id);

        // if company doesn't exist get the company id from the stored_match_data and update it
        if (!isset($company_id)) {
            // set the key to find
            $key = 'Company.Company.' . strtolower($company);

            Log::info("Linking Company to Contact - Company doesn't exist, searching in stored_match_data. key - " . $key);

            // check if company exists
            $exists = array_has($this->stored_match_data, $key);

            // if company exists, get the id
            if ($exists) {
                $company_id = array_get($this->stored_match_data, $key);
                Log::info("Linking Company to Contact - Company exists in stored_match_data. company_id - " . $company_id);
            } else {
                Log::info("Linking Company to Contact - Company doesn't exist in stored_match_data. key - " . $key);
            }
        }

        // lastly, fill only the array if they are not null
        if (isset($company_id)) {
            $data['CompanyID'] = $company_id;
        }

        if (isset($company)) {
            $data['Company'] = $company;
        }

        // update only when company data is set
        if (isset($company_id) || isset($company)) {
            $this->infusionsoftService->update($id, $data, $type, $user_id);
        }
    }

    /**
     * When adding a new INFS item, the stored_match_data will be outdated.
     * This would cause duplication issues.
     * In order to solve this, add the new infs item to the stored_matched_data.
     *
     * @param $id
     * @param $data
     * @param $type
     * @param $options
     */
    public function updateStoredMatchingData($id, $data, $type, $options) {
        // get obj type
        $obj_type = Helpers::getMatchingFieldByInfs($type);

        // get all matching fields
        $all_matching_fields = Helpers::getValueOrNull($options, 'matching_fields', Helpers::VALUE_ARR);

        // get matching field by type
        $matching_fields = Helpers::getValueOrNull($all_matching_fields, $obj_type, Helpers::VALUE_ARR);

        // loop each matching field and add it to the stored_match_data
        foreach($matching_fields as $match_field) {
            $data_match_field = Helpers::getValueOrNull($data, $match_field, Helpers::VALUE_ARR);

            if (isset($data_match_field)) {
                $data_match_field = strtolower($data_match_field);
                $this->stored_match_data[$type][$match_field][$data_match_field] = $id;
            }
        }
    }

    /**
     * Update the csv import record
     *
     * @param $id
     * @param $csv_import_record
     * @param $type
     * @param $action
     */
    public function updateCsvImportRecord($id, $csv_import_record, $type, $action) {
        // get csv import record infs field
        $infs_field_match = Helpers::getImportRecordInfsMatchField($type);

        // supply parameters
        $parameters = new ParameterBag;
        $parameters->set($infs_field_match['field'], $id);
        $parameters->set($infs_field_match['match'], $action);

        // update the record
        $this->csv_import_record_repo->update($csv_import_record->id, $parameters->all());
    }

    /**
     * Check for failed syncs
     *
     * @return string
     */
    public function syncFailed() {
        $csv_imports = $this->csv_import_repo->getRunningSync()->get();

        $now = Carbon::now();

        Log::info("Checking for failed syncs..");

        foreach($csv_imports as $csv_import) {
            // get number of minutes until csv import expires
            $wait_minutes = Helpers::getWaitMinutes($csv_import->id);

            // check if this csv import took too long to process
            $expired = $this->csv_import_repo->checkExpiryDate($csv_import->started_at, $now, $wait_minutes);

            if ($expired) {
                Log::info("Wait minutes of - " . $wait_minutes . " mins has been consumed. Csv import id - " . $csv_import->id);

                $this->csv_import_repo->markAsInfsFailedSync($csv_import->id);
            }
        }
    }

    /**
     * Mark both csv import and record as failed whenever there are issues with infs sync
     *
     * @param $options
     */
    public function markFailed($options) {
        // mark as failed sync
        $csv_import = Helpers::getValueOrNull($options, 'csv_import', Helpers::VALUE_ARR);
        $csv_import_record = Helpers::getValueOrNull($options, 'csv_import_record', Helpers::VALUE_ARR);

//        if (isset($csv_import)) {
//            $this->csv_import_repo->markAsInfsFailedSync($csv_import->id);
//        }

        if (isset($csv_import_record)) {
            $this->csv_import_record_repo->markAsCsvRecordFailedSync($csv_import_record->id);
        }
    }
}
