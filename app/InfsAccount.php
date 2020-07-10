<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsAccount extends Model
{
    protected $table = 'infusionsoft_accounts';
    
    public $timestamps = true;
    
    public $incrementing = false;
    
    protected $fillable = [
        'user_id',
        'name',
        'access_token',
        'referesh_token',
        'expire_date',
        'refresh_token_expired_at',
        'account',
        'active',
        'error_reported',
        'client_id',
        'client_secret'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function companyContactMap()
    {
        return $this->hasMany('App\CompanyContactMap', 'infs_account_id');
    }

    public function infsSync()
    {
        return $this->hasOne('App\InfsSync', 'infs_account_id');
    }
}
