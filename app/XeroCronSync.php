<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XeroCronSync extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','xero_id', 'infusionsoft_account_id', 'settings', 'status'
    ];
    
    public $timestamps = false;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected $table = "xero_cron_sync";
}
