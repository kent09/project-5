<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FusePremiumModes extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'function_name'
    ];

    protected $table = "fuse_plans_access";
}
