<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUsageCsv extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','records_imported','reset_date'
    ];

    protected $table = "user_usage_csv";
}
