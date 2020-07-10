<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostcOwnerGroups extends Model
{
    protected $table = 'postc_owner_groups';
    
    public $timestamps = true;
    
    protected $fillable = [
        'postc_owner_id',
        'status',
        'match_type',
        'postc_country_code',
        'postc_code',
        'postc_radius',
        'postc_units',
        'postc_list'
    ];
}
