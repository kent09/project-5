<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XeroAccounts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','app_id', 'app_name', 'oauth_token', 'oauth_token_secret', 'oauth_expires_in'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected $table = "xero_accounts";
}
