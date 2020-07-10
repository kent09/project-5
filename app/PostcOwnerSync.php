<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostcOwnerSync extends Model
{
    protected $table = 'postc_owner_sync';
    
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'infs_account_id',
        'infs_person_id',
        'status',
        'start_date_time',
        'finish_date_time',
        'contacts_updated',
        'opp_updated',
    ];
    
    public function group()
    {
        // return $this->hasMany('App\PostcOwnerGroups', 'postc_owner_id');
    }
}
