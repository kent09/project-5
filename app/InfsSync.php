<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsSync extends Model
{
    //
    protected $table = 'infs_sync_config';

    protected $guarded = [];

    public function infusionsoftAccount()
    {
        return $this->belongsTo('App\InfsAccount', 'infs_account_id');
    }
}
