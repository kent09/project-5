<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScriptHttpPosts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','infs_account_id','contactId', 'mode', 'status', 'post_data' ,'response_data'
    ];

    protected $table = "script_http_posts";
}
