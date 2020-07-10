<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XeroCronSyncResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'xero_cron_sync_id','status','start_time', 'end_time', 'orders_synced', 'next_scheduled'
    ];
    
    public $timestamps = false;


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected $table = "xero_cron_sync_result";
}
