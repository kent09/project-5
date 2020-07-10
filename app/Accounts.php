<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{

    protected $fillable = [
        'owner_id',
        'account_name'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
