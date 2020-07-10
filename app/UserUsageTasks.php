<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserUsageTasks extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','tasks_used','account','reset_date'
    ];

    protected $table = "user_usage_tasks";
}
