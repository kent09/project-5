<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostcCodesCa extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'postc_codes_ca';
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'region1',
        'region2',
        'region3',
        'postcode',
        'suburb',
        'latitude',
        'longitude',
    ];
}
