<?php

namespace App\Repositories;

use App\CsvImports;
use App\Helpers\CsvStatus;
use App\Helpers\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\ParameterBag;

class CsvImportRepository extends Repository {

    protected $user;

    public function __construct() {
        $this->model = new CsvImports;

        $this->repo_type = Helpers::REPO_CSV_IMPORTS;

        $this->user = UserRepository::getUser();

    }

    /**
     * Get items to be showed on the index
     *
     * @return mixed
     */
    public function index() {

        $imports = CsvImports::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->orderBy('import_title', 'desc')
            ->orderBy('status', 'desc')->paginate(10);

        return $imports;
    }

    /**
     * Get all records of an import
     *
     * @param $id
     * @return mixed
     */
    public function getRecords($id) {
        $item = $this->find($id);

        $csv_import_records = $item->records();

        return $csv_import_records;
    }

    /**
     * Get records that are currently importing
     *
     * @param $id
     * @return mixed
     */
    public function importing($id) {
        $item = $this->find($id);

        return $item->records()->where('status', CsvStatus::IMPORTING);
    }

    /**
     * Get all finished imports
     *
     * @return mixed
     */
    public function importFinished() {
        $items = CsvImports::where('status', CsvStatus::IMPORT_FINISHED);

        return $items;
    }

    /**
     * Get all running imports
     *
     * @return mixed
     */
    public function runningSync() {
        $items = CsvImports::where('status', CsvStatus::RUNNING_SYNC);

        return $items;
    }

    /**
     * Get all finished imports
     * @return mixed
     */
    public function finishedSync() {
        $items = CsvImports::where('status', CsvStatus::FINISHED_SYNC);

        return $items;
    }

    /**
     * Get running infs syncs
     *
     * @return mixed
     */
    public function infsRunningSync() {
        $items = CsvImports::where('status', CsvStatus::INFS_RUNNING_SYNC);

        return $items;
    }

    /**
     * Get infs finished syncs
     *
     * @return mixed
     */
    public function infsFinishedSync() {
        $items = CsvImports::where('status', CsvStatus::INFS_FINISHED_SYNC);

        return $items;
    }

    /**
     * Get infs failed syncs
     *
     * @return mixed
     */
    public function infsFailedSync() {
        $items = CsvImports::where('status', CsvStatus::INFS_FAILED_SYNC);

        return $items;
    }

    /**
     * Mark item as import finished
     *
     * @param $id
     * @return \Exception|mixed
     */
    public function markAsImportFinished($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::IMPORT_FINISHED);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Mark item as running sync
     *
     * @param $id
     * @return $mixed
     */
    public function markAsRunningSync($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::RUNNING_SYNC);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Mark item as finished sync
     *
     * @param $id
     * @return \Exception|mixed
     */
    public function markAsFinishedSync($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('completed_at', Carbon::now());
        $parameters->set('status', CsvStatus::FINISHED_SYNC);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Mark item as import failed
     *
     * @param $id
     * @return \Exception|mixed
     */
    public function markAsImportError($id)
    {
        Log::info("Marked as failed import - Csv import id - " . $id);

        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::IMPORT_ERROR);

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Item is already being processed to infs
     * Mark it as INFS RUNNING SYNC
     *
     * @param $id
     * @return mixed
     */
    public function markAsInfsRunningSync($id) {
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::INFS_RUNNING_SYNC);
        $parameters->set('started_at', Carbon::now());

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * The item has finished syncing to infs
     * Mark it as INFS FINISHED SYNC
     *
     * @param $id
     * @return mixed
     */
    public function markAsInfsFinishedSync($id) {
        $now = Carbon::now();
        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::INFS_FINISHED_SYNC);
        $parameters->set('completed_at', $now->toDateTimeString());

        $result = $this->update($id, $parameters->all());

        return $this->result($id, $result);
    }

    /**
     * Something went wrong while syncing to infs
     * Mark it as INFS FAILED SYNC
     *
     * @param $id
     * @return mixed
     */
    public function markAsInfsFailedSync($id) {
        Log::info("Marked as failed sync - Csv import id - " . $id);

        // set parameters
        $parameters = new ParameterBag;
        $parameters->set('status', CsvStatus::INFS_FAILED_SYNC);

        $result = $this->update($id, $parameters->all());

        Helpers::sendInfsFailedEmail($id);

        return $this->result($id, $result);
    }

    /**
     * Get all imports that are currently running
     *
     * @return mixed
     */
    public function getRunningSync() {
        $csv_imports = CsvImports::where('status', CsvStatus::INFS_RUNNING_SYNC);

        return $csv_imports;
    }

    /**
     * Return the default title of the csv import
     *
     * @return string
     */
    public function getDefaultTitle() {
        return CsvImports::TITLE_DRAFT;
    }

    /**
     * Get the header fields from the csv
     *
     * @param $id
     * @return array
     */
    public function getCsvHeaderFields($id) {
        $csv_import = $this->find($id);

        // get first record
        $csv_import_record = $csv_import->records()->limit(1)->first();

        $res = [];

        if ($csv_import_record->data) {
            $arr = (array) $csv_import_record->data;
            $res = array_keys($arr);
        }

        return $res;
    }

    /**
     * Get matching rules
     *
     * @param $id
     * @param null $type
     * @return |null
     */
    public function getMatchingRules($id, $type = null) {
        $result = null;

        $csv_import = $this->find($id);

        $import_results = $csv_import->import_results;

        // get matching rules
        $result = isset($import_results['match']) ? $import_results['match'] : null;

        // get matching rules by type if type is not null
        $result = isset($import_results['match']) && isset($type) && isset($import_results['match'][$type])
            ? $import_results['match'][$type]
            : $result;

        return $result;
    }

    /**
     * Get filters
     *
     * @param $id
     */
    public function getFilters($id) {
        $results = null;

        $csv_import = $this->find($id);

        $import_results = $csv_import->import_results;

        // get filter types
        $filter_types = $this->getFilterTypes();

        foreach($filter_types as $type => $filter) {
            $value = isset($import_results[$filter]) ? $import_results[$filter] : null;

            if (isset($value)) {
                $results[$type] = $value;
            }
        }

        return $results;
    }

    /**
     * Get the filter types
     *
     * @return array
     */
    public function getFilterTypes() {
        return [
            CsvStatus::TYPE_COMPANY => CsvImports::FILTER_COMPANY,
            CsvStatus::TYPE_CONTACT => CsvImports::FILTER_CONTACT,
        ];
    }

    /**
     * Get the email opt in values from the import results
     *
     * @param $id
     * @return array
     */
    public function getOptin($id) {
        $csv_import = $this->find($id);

        // get the import results
        $import_results = $csv_import->import_results;

        // get the email opt in
        $email_opt_in = Helpers::getValueOrNull($import_results, 'email_opt_in', Helpers::VALUE_ARR);

        // get email opt in reason
        $email_opt_in_reason = Helpers::getValueOrNull($import_results, 'email_opt_in_reason', Helpers::VALUE_ARR);

        $results = [
            'email_opt_in' => $email_opt_in,
            'email_opt_in_reason' => $email_opt_in_reason
        ];

        return $results;
    }

    /**
     * Get the tags from this import
     *
     * @param $id
     * @return array
     */
    public function getTags($id) {
        $csv_import = $this->find($id);

        // get the import results
        $import_results = $csv_import->import_results;

        // get the email opt in
        $tags = Helpers::getValueOrNull($import_results, 'tags', Helpers::VALUE_ARR);

        return $tags;
    }

    /**
     * Check if date has expired
     *
     * @param $date string
     * @param $current Carbon
     * @param $wait_minutes
     * @return bool
     */
    public function checkExpiryDate($date, $current = null, $wait_minutes = Helpers::WAIT_MINUTES) {
        // get and convert $date to carbon instance
        $start = Carbon::parse($date);

        // get current time
        $now = isset($current)
            ? $current
            : Carbon::now();

        return $now->diffInMinutes($start) > $wait_minutes ;
    }
}