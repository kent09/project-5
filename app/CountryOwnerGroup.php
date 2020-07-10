<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryOwnerGroup extends Model
{
    protected $table = 'country_owner_groups';

    protected $fillable = [
        'country_owner_id',
        'infs_country_id',
        'status',
    ];

    public function country_owner()
    {
        return $this->belongsTo(CountryOwner::class, 'country_owner_id');
    }

    public function country()
    {
        return $this->hasOne(InfsCountry::class, 'id', 'infs_country_id');
    }
}
