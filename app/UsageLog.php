<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    protected $table = "usage_log";

    public function subscription()
    {
        return $this->belongsTo('App\UserSubscription');
    }
}
