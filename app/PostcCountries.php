<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostcCountries extends Model
{
    protected $table = 'postc_countries';
    
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    public $incrementing = false;
    
    protected $fillable = [
        'country_name',
        'country_code',
    ];
}
