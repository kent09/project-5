<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CsvImportsRecs extends Model
{
    protected $table = 'csv_imports_recs';
    
    public $timestamps = true;
    
    protected $fillable = ['import_id','record_type','raw_datetime','raw_data','normalised_datetime','normalised_data','infs_record_id','infusionsoft_datetime','new_existing','status'];
}
