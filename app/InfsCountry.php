<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsCountry extends Model
{
    protected $table = 'infs_countries';

    protected $fillable = [
        'country_name',
        'country_code'
    ];
}
