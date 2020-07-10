<?php

namespace App\Helpers;

use App\Repositories\CsvImportRecordRepository;
use App\Repositories\CsvImportRepository;
use App\Repositories\InfsCompanyMetaRepository;
use App\Repositories\InfsCompanyRepository;
use App\Repositories\InfsContactMetaRepository;

class CsvToInfsMapper {

    const MAP_CONTACT = 'contacts';
    const MAP_COMPANY = 'company';
    const MAP_ORDER = 'orders';
    const MAP_PRODUCT= 'products';
    const ORDER_TYPE_LINE_ITEM_ORDER = 1;
    const ORDER_TYPE_LINE_NEW_ORDER = 2;

    /**
     * Map fields by type
     *
     * @param $type
     * @param $csv_import
     * @param $csv_import_record
     * @return array|null
     */
    public static function mapByType($type, $csv_import, $csv_import_record) {
        $results = null;

        // get the mapped data
        switch($type) {
            case CsvStatus::INFS_CONTACT:
                $results = CsvToInfsMapper::contact($csv_import, $csv_import_record);
                break;
            case CsvStatus::INFS_COMPANY:
                $results = CsvToInfsMapper::company($csv_import, $csv_import_record);
                break;
            default:
                $results = null;
                break;
        }

        return $results;
    }

    /**
     * Map the company
     *
     * @param $csv_import
     * @param $csv_import_record
     * @return array
     */
    public static function company($csv_import, $csv_import_record) {
        return self::map($csv_import, $csv_import_record, self::MAP_COMPANY);
    }

    /**
     * Map the product
     *
     * @param $csv_import
     * @param $csv_import_record
     * @return array
     */
    public static function product($csv_import, $csv_import_record) {
        return self::map($csv_import, $csv_import_record, self::MAP_PRODUCT);
    }


    /**
     * Map the contacts
     *
     * @param $csv_import
     * @param $csv_import_record
     * @return array
     */
    public static function contact($csv_import, $csv_import_record) {
        $mapped_fields = self::map($csv_import, $csv_import_record, self::MAP_CONTACT);

        // get additional contact fields
        $additional_fields = self::contactAdditionalFields($csv_import, $csv_import_record, $mapped_fields);

        // loop each item and add them to the mapped fields
        foreach($additional_fields as $key => $value) {
            $mapped_fields[$key] = $value;
        }

        return $mapped_fields;
    }

    /**
     * Map the order
     *
     * @param $csv_import
     * @param $csv_import_record
     * @return array
     */
    public static function order($csv_import, $csv_import_record) {
        // @todo: check the settings

        // result of the mapper
        $result = [];

        // set the map type
        $type = self::MAP_ORDER;

        // use the csv_settings instead of csv_import get the order id field
        $settings = $csv_import->csv_settings;

        // return null if 'type' doesn't exist
        if (!property_exists($settings, $type)) {
            return null;
        }

        // get only the "type" (either contact, order, company etc) part of the settings
        $type_settings = $settings->{$type}->fields;

        // get data
        $data = $csv_import_record->data;

        // map the fields
        foreach ($type_settings as $csv_field => $infs_field) {
            $result[$infs_field] = $data->{$csv_field};
        }

        return $result;
    }

    /**
     * Dynamically map the fields
     *
     * @param $csv_import
     * @param $csv_import_record
     * @param $type
     * @return array
     */
    public static function map($csv_import, $csv_import_record, $type) {
        // result of the mapper
        $result = [];

        // get settings for this csv import
        $settings = $csv_import->csv_settings;

        // return null if it is not existing
        if (!array_key_exists($type, $settings)) {
            return null;
        }

        // get only the type part of the settings
        $type_settings = $settings[$type];

        // get data
        $data = $csv_import_record->data;

        // map the fields
        foreach ($type_settings as $csv_field => $infs_field) {
            $field = $infs_field;
            $result[$field] = $data->{$csv_field};
        }

        return $result;
    }

    /**
     * Map all fields
     *
     * @param $items
     * @param $csv_import
     * @param $csv_import_record
     * @return mixed
     */
    public static function mapAll($items, $csv_import, $csv_import_record) {
        $results = $items;

        // loop each item, we're just interested on the key
        foreach($items as $key => $value) {
            switch ($key) {
                case self::MAP_COMPANY:
                    $item = self::company($csv_import, $csv_import_record);
                    break;
                case self::MAP_CONTACT:
                    $item = self::contact($csv_import, $csv_import_record);
                    break;
            }

            $results = Helpers::storeValuesFromMap($results, $item, $key);
        }

        // exclude if content is null
        $results = self::excludeEmptyMap($results);

        return $results;
    }

    /**
     * Exclude items from map if empty
     *
     * @param $mapped_items
     * @return null
     */
    public static function excludeEmptyMap($mapped_items) {
        $results = null;
        foreach($mapped_items as $type => $items) {
            if (isset($items)) {
                $results[$type] = $items;
            }
        }

        return $results;
    }

    /**
     * Add contact-related company fields here
     *
     * @param $csv_import
     * @param $csv_import_record
     * @param $mapped_fields
     * @return array
     */
    public static function contactAdditionalFields($csv_import, $csv_import_record, $mapped_fields) {
        // store all the results here
        $results = [];

        // get company related items
        // $results = Helpers::getCompanyAsAdditionalField($results, $csv_import, $csv_import_record, $mapped_fields);

        // get opt in
        $results = Helpers::getContactOptInAsAdditionalField($results,$csv_import);

        return $results;
    }

    /**
     * Consolidate the orders to a single line
     *
     * @param $csv_import
     * @return array|mixed
     */
//    public static function eachLineItemOrder($csv_import) {
//        // result of the process
//        $results = [
//            self::MAP_CONTACT => [],
//            self::MAP_COMPANY => [],
//            self::MAP_PRODUCT => [],
//            self::MAP_ORDER => [],
//        ];
//
//        // initialize repositories
//        $csv_import_repo = new CsvImportRepository;
//        $csv_import_record_repo = new CsvImportRecordRepository;
//
//        // get all pending records under this csv import
//        $csv_import_records = $csv_import_record_repo->getPendingRecords($csv_import->id);
//
//        // loop each item
//        foreach($csv_import_records as $csv_import_record) {
//            $results = self::process($results, $csv_import, $csv_import_record, self::MAP_CONTACT);
//
//            $results = self::process($results, $csv_import, $csv_import_record, self::MAP_COMPANY);
//
//            $results = self::process($results, $csv_import, $csv_import_record, self::MAP_PRODUCT);
//
//            $results = self::process($results, $csv_import, $csv_import_record, self::MAP_ORDER);
//        }
//
//        return $results;
//    }

    /**
     * Consolidate the orders to a single line
     *
     * @param $csv_import
     * @param $csv_import_record
     * @return array|mixed
     */
//    public static function eachLineNewOrder($csv_import, $csv_import_record) {
//        // result of the process
//        $results = [
//            self::MAP_CONTACT => [],
//            self::MAP_COMPANY => [],
//            self::MAP_PRODUCT => [],
//            self::MAP_ORDER => [],
//        ];
//
//        // @todo
//
//
//    }

//    public static function process($items, $csv_import, $csv_import_record, $type) {
//        // check if record already exists on local db
//        $exists = Helpers::checkDuplicates($csv_import, $csv_import_record, $type);
//
//        // exit if item already exists
//        if ($exists) {
//            // @todo: check setting for update
//            return false;
//        }
//
//        switch($type) {
//            case self::MAP_CONTACT:
//                $item = self::contact($csv_import, $csv_import_record);
//                break;
//            case self::MAP_COMPANY:
//                $item = self::company($csv_import, $csv_import_record);
//                break;
//            case self::MAP_PRODUCT:
//                $item = self::product($csv_import, $csv_import_record);
//                break;
//            case self::MAP_ORDER:
//                $item = self::order($csv_import, $csv_import_record);
//                break;
//        }
//
//        $results = Helpers::storeValuesFromMap($items, $item, $type);
//
//        return $results;
//    }
}
