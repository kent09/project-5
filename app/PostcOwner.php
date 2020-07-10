<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostcOwner extends Model
{
    protected $table = 'postc_owner';
    
    public $timestamps = true;
    
    protected $fillable = [
        'user_id',
        'infs_account_id',
        'infs_person_id',
        'owner_name',
        'status'
    ];
    
    public function group()
    {
        return $this->hasMany('App\PostcOwnerGroups', 'postc_owner_id');
    }
}
