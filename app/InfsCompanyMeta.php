<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsCompanyMeta extends Model
{
    protected $table = 'infs_companies_meta';

    public $timestamps = true;

    protected $fillable = [
        'company_id',
        'field_name',
        'field_value'
    ];

}
