<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyContactMap extends Model
{
    //
    protected $table = 'company_contact_mapping';

    protected $guarded = [];

    public function infusionsoftAccount()
    {
        return $this->belongsTo('App\InfsAccount', 'infs_account_id');
    }
}
