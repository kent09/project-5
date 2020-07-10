<?php

namespace App;

use App\Helpers\CsvStatus;
use Illuminate\Database\Eloquent\Model;

class CsvImportRecord extends Model {

    const MATCHED = 'matched';
    const CREATED = 'created';

    protected $table = 'csv_import_records';

    public $timestamps = true;

    protected $fillable = [
        'csv_import_id',
        'infs_contact',
        'infs_company',
        'infs_order',
        'con_created_matched',
        'comp_created_matched',
        'order_created_matched',
        'data',
        'status',
    ];

    /**
     * Scopes
     */
    public static function pending() {
        return self::where('status', CsvStatus::CSV_RECORD_PENDING);
    }

    /**
     * Accessors / Mutators
     */

    public function getDataAttribute() {
        return json_decode($this->attributes['data']);
    }

    /**
     * Relationships
     */

    public function csvImport() {
        return $this->belongsTo('App\CsvImports', 'csv_import_id');
    }

    /**
     * Helpers
     */

    public static function getInfsContactField() {
        return 'infs_contact';
    }

    public static function getInfsContactFieldMatched() {
        return 'con_created_matched';
    }

    public static function getInfsCOmpanyField() {
        return 'infs_company';
    }

    public static function getInfsCompanyFieldMatched() {
        return 'comp_created_matched';
    }
}