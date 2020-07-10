<?php

namespace App;

use App\Helpers\CsvStatus;
use App\Repositories\CsvImportRecordRepository;
use Illuminate\Database\Eloquent\Model;

class CsvImports extends Model
{
    const TITLE_DRAFT = 'DRAFT';
    const FILTER_CONTACT = 'filter_contact';
    const FILTER_COMPANY = 'filter_company';

    const FILTER_BOTH = 'both';
    const FILTER_CREATE = 'create';
    const FILTER_UPDATE= 'update';

    protected $table = 'csv_imports';
    
    public $timestamps = true;

    protected $casts = [
        'csv_settings' => 'array',
        'import_results' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'account_id',
        'import_title',
        'status',
        'started_at',
        'completed_at',
        'csv_file',
        'csv_settings',
        'import_results',
        'step'
    ];

    protected $appends = [
        '_status_str',
        '_contacts_created',
        '_companies_created'
    ];

    public function sync()
    {
        return $this->hasMany('App\FuseImportsSync', 'fuse_imports_id');
    }
    
    public function infsAccount()
    {
        return $this->hasOne('App\InfsAccount', 'id', 'account_id');
    }

    /**
     * Mutators / Accessors
     */

    public function getStatusStrAttribute() {
        $result = 'Importing';
        switch($this->attributes['status']) {
            case CsvStatus::IMPORTING:
                $result = self::TITLE_DRAFT;
                break;
            case CsvStatus::IMPORT_FINISHED:
                $result = 'Processing';
                break;
            case CsvStatus::RUNNING_SYNC:
                $result = 'Processing';
                break;
            case CsvStatus::FINISHED_SYNC:
                $result = 'Queue to Infs ';
                break;
            case CsvStatus::INFS_RUNNING_SYNC:
                $result = 'Syncing to Infs';
                break;
            case CsvStatus::INFS_FINISHED_SYNC:
                $result = 'Complete';
                break;
            case CsvStatus::INFS_FAILED_SYNC:
                $result = 'Error';
                break;
            case CsvStatus::IMPORT_ERROR:
                $result = 'Error';
                break;
        }

        return $result;
    }

    public function getContactsCreatedAttribute() {
        $csv_import_record_repo = new CsvImportRecordRepository;

        return $csv_import_record_repo->countCreatedType($this->attributes['id'], CsvStatus::TYPE_CONTACT);
    }

    public function getCompaniesCreatedAttribute() {
        $csv_import_record_repo = new CsvImportRecordRepository;

        return $csv_import_record_repo->countCreatedType($this->attributes['id'], CsvStatus::TYPE_COMPANY);
    }

    /**
     * Scopes
     *
     * PS: Not running on PHP 7.3 because of Countable issue
     */

    public function scopeImporting($query) {
        return $query->where('status', CsvStatus::IMPORTING);
    }

    public function scopeImportFinished($query) {
        return $query->where('status', CsvStatus::IMPORT_FINISHED);
    }

    public function scopeRunningSync($query) {
         return $query->where('status', CsvStatus::RUNNING_SYNC);
    }

    public function scopeFinishedSync($query) {
        return $query->where('status', CsvStatus::FINISHED_SYNC);
    }

    public function scopeInfsRunningSync($query) {
        return $query->where('status', CsvStatus::INFS_RUNNING_SYNC);
    }

    public function scopeInfsFinishedSync($query) {
        return $query->where('status', CsvStatus::INFS_FINISHED_SYNC);
    }

    public function scopeInfsFailedSync($query) {
        return $query->where('status', CsvStatus::INFS_FAILED_SYNC);
    }

    /**
     * Relationships
     */

    public function records() {
        return $this->hasMany('App\CsvImportRecord', 'csv_import_id');
    }

    public function hasSettings($type) {
        $settings = $this->csv_settings;

        return isset($settings[$type]);
    }

    public function getSettingsKey($type, $key) {
        $settings = $this->csv_settings;

        return isset($settings[$type][$key]) ? $settings[$type][$key] : null;
    }

    public function createdContacts() {
        return $this->hasMany('App\CsvImportRecord', 'csv_import_id')
            ->where('con_created_matched', CsvImportRecord::CREATED);
    }

    public function matchedContacts() {
        return $this->hasMany('App\CsvImportRecord', 'csv_import_id')
            ->where('con_created_matched', CsvImportRecord::MATCHED);
    }

    public function createdCompanies() {
        return $this->hasMany('App\CsvImportRecord', 'csv_import_id')
            ->where('comp_created_matched', CsvImportRecord::CREATED);
    }

    public function matchedCompanies() {
        return $this->hasMany('App\CsvImportRecord', 'csv_import_id')
            ->where('comp_created_matched', CsvImportRecord::MATCHED);
    }

}
