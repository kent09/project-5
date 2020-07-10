<?php

namespace App\Repositories;

use App\CsvImportRecord;
use App\Helpers\CsvStatus;
use App\Helpers\CsvToInfsMapper;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\ParameterBag;

class CsvImportRecordRepository extends Repository{

    const REPO_TYPE = 'csv_import_records';

    public function __construct() {
        $this->model = new CsvImportRecord;

        $this->repo_type = Helpers::REPO_CSV_IMPORT_RECORD;
    }

    /**
     * Get all pending records
     *
     * @param $csv_import_id
     * @return mixed
     */
    public function pending($csv_import_id) {
        $items = CsvImportRecord::where('status', CsvStatus::CSV_RECORD_PENDING)
            ->where('csv_import_id', $csv_import_id)
            ->orderBy('id', 'asc');

        return $items;
    }

    /**
     * Get import processing items
     *
     * @param $csv_import_id
     * @return mixed
     */
    public function importProcessing($csv_import_id) {
        $items = CsvImportRecord::where('status', CsvStatus::CSV_RECORD_IMPORT_PROCESSING)
            ->where('csv_import_id', $csv_import_id)
            ->orderBy('id', 'asc');

        return $items;
    }

    /**
     * Get import success items
     *
     * @param $csv_import_id
     * @return mixed
     */
    public function importSuccess($csv_import_id) {
        $items = CsvImportRecord::where('status', CsvStatus::CSV_RECORD_IMPORT_SUCCESS)
            ->where('csv_import_id', $csv_import_id)
            ->orderBy('id', 'asc');

        return $items;
    }

    /**
     * Item is being made by the user
     * Mark item as PENDING
     *
     * @param $id
     * @return $mixed
     */
    public function markAsCsvRecordPending($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_PENDING);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * The item is currently being processed by a cron
     * Mark item as IMPORT PROCESSING
     *
     * @param $id
     * @return $mixed
     */
    public function markAsCsvRecordImportProcessing($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_IMPORT_PROCESSING);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Cron has finished processing the item
     * Mark item as IMPORT SUCCESS
     *
     * @param $id
     * @return $mixed
     */
    public function markAsCsvRecordSuccess($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_IMPORT_SUCCESS);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * The record will start syncing to infs
     * Mark item as INFS_RUNNING_SYNC
     *
     * @param $id
     * @return $mixed
     */
    public function markAsCsvRecordInfsRunningSync($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_INFS_RUNNING_SYNC);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * The record has finished syncing to Infusionsoft
     * Mark item as INFS_FINISHED_SYNC
     *
     * @param $id
     * @return $mixed
     */
    public function markAsCsvRecordInfsFinishedSync($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_INFS_FINISHED_SYNC);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * The record had some errors.
     * Mark item as INFS_FAILED_SYNC
     *
     * @param $id
     * @return mixed
     */
    public function markAsCsvRecordFailedSync($id) {
        Log::info("Marked as failed sync - Csv import record id - " . $id);

        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::CSV_RECORD_INFS_FAILED_SYNC);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }


    /**
     * Each record has infs types (company, contact, etc)
     * These types will be marked as CREATED or MATCHED
     * CREATED - this is when the infs item is first created (infs_contact, infs_company, etc)
     * MATCHED - if the infs item already exists on the database
     *
     * @param $id
     * @param $mapped_items
     * @return \Exception
     */
    public function updateMatchingFields($id, $mapped_items) {
        // get csv import repo
        $csv_import_repo = new CsvImportRepository;

        // get csv import record instance
        $csv_import_record = $this->find($id);

        // get csv import
        $csv_import = $csv_import_record->csvImport()->first();

        // initialize infs records
        $infs_company = null;
        $infs_contact = null;
        $infs_order = null;

        // foreach mapped records
        foreach($mapped_items as $type => $item) {

            // get repository
            $repo = Helpers::getRepository($type);

            // get csv rules
            $rules = $csv_import_repo->getMatchingRules($csv_import->id, $type);

            // find the infs record based from its type
            switch($type) {
                case CsvToInfsMapper::MAP_COMPANY:
                    $infs_company = $repo->findBymap($item, $rules);
                    $infs_company_exists = $this->checkIfCreatedOrMatched($infs_company, $type);
                    break;
                case CsvToInfsMapper::MAP_CONTACT:
                    $infs_contact = $repo->findBymap($item, $rules);
                    $infs_contact_exists = $this->checkIfCreatedOrMatched($infs_contact, $type);
                    break;
            }
        }

        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('infs_contact', Helpers::getValueOrNull($infs_contact, 'id'));
        $parameters->set('con_created_matched', isset($infs_contact_exists) ? $infs_contact_exists : null);
        $parameters->set('infs_company', Helpers::getValueOrNull($infs_company, 'id'));
        $parameters->set('comp_created_matched', isset($infs_company_exists) ? $infs_company_exists : null);

        // mark record as success
        $parameters->set('status', CsvStatus::CSV_RECORD_IMPORT_SUCCESS);

        return $this->update($id, $parameters->all());
    }

    /**
     * Checks if the item was already created.
     * If yes, mark it as matched.
     *
     * @param $item
     * @param $type
     * @return string
     */
    public function checkIfCreatedOrMatched($item, $type) {
        // the variable used to store the field to be checked
        $field_to_check = null;

        // get the field to check
        switch($type) {
            case CsvToInfsMapper::MAP_COMPANY:
                $field_to_check = 'infs_company';
                break;
            case CsvToInfsMapper::MAP_CONTACT:
                $field_to_check = 'infs_contact';
                break;
        }

        // initialize item id
        $item_id = Helpers::getValueOrNull($item, 'id');

        // check the field if it exists on other csv import records
        $item = CsvImportRecord::where($field_to_check, $item_id)->first();

        return isset($item)
            ? CsvImportRecord::MATCHED
            : CsvImportRecord::CREATED;
    }

    /**
     * Count created infs items by type
     *
     * @param $csv_import_id
     * @return int
     */
    public function countCreatedType($csv_import_id, $type) {
        $records = CsvImportrecord::where('csv_import_id', $csv_import_id)->get();

        $count = $records->reject(function ($item, $key) use ($type) {
            switch($type) {
                case CsvStatus::TYPE_CONTACT:
                    return $item->con_created_matched !== CsvImportRecord::CREATED;
                    break;
                case CsvStatus::TYPE_COMPANY:
                    return $item->comp_created_matched !== CsvImportRecord::CREATED;
                    break;
            }
        });

        return count($count);
    }

    /**
     * Get record using the infs field
     *
     * @param $infs_id
     * @param $infs_field
     * @return mixed
     */
    public function getByInfs($infs_id, $infs_field) {
        return CsvImportRecord::where($infs_field, $infs_id)->first();
    }

    /**
     * Get the infs_contact field from model
     *
     * @return string
     */
    public function getInfsContactField() {
        return CsvImportRecord::getInfsContactField();
    }

    /**
     * Get the company field from model
     *
     * @return string
     */
    public function getInfsCompanyField() {
        return CsvImportRecord::getInfsCompanyField();
    }

    /**
     * Get the con_matched field from model
     *
     * @return string
     */
    public function getInfsContactFieldMatched() {
        return CsvImportRecord::getInfsContactFieldMatched();
    }

    /**
     * Get the comp_matched field from model
     *
     * @return string
     */
    public function getInfsCompanyFieldMatched() {
        return CsvImportRecord::getInfsCompanyFieldMatched();
    }

    /**
     * Map order fields
     *
     * @param $csv_import_id
     */
    public function eachLineItemOrder($csv_import_id) {
        // $results = [];

        // initialize csv import repository
        $csv_import_repo = new CsvImportRepository;

        // get csv import settings
        $csv_import = $csv_import_repo->find($csv_import_id);

        // get all pending records under this csv import
        // $csv_import_records = $this->getPendingRecords($csv_import_id);

        $results = CsvToInfsMapper::eachLineItemOrder($csv_import);

        dd(json_encode($results));
        // get order items
//        foreach($csv_import_records as $csv_import_record) {
//
//            // generate contact for each record
//            //$contact = CsvToInfsMapper::contact($csv_import, $csv_import_record);
//
//            // generate company for each record
//            //$company = CsvToInfsMapper::company($csv_import, $csv_import_record);
//
//            // $products = CsvToInfsMapper::map($csv_import, $csv_import_record, CsvToInfsMapper::MAP_ORDER);
//
//            $order = CsvToInfsMapper::order($csv_import, $csv_import_record);
//
//            array_push($results, $order);
//            // dd($contact);
//        }

        dd($results);

    }


}