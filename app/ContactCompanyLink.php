<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactCompanyLink extends Model
{
    //
    protected $table = 'contact_company_links';

    protected $guarded = [];

    public function infusionsoftAccount()
    {
        return $this->belongsTo('App\InfsAccount', 'infs_account_id');
    }
}
