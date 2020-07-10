<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUsageDocs extends Model
{
    protected $table = 'user_usage_docs';
   
    public $timestamps = true;
   
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'docs_sent','user_id','reset_date'
    ];
}
