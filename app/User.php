<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Notifiable;
    use Billable;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'FuseKey',
        'email',
        'password',
        'invitation_token',
        'active',
        'post_code',
        'infusionsoft_contact_id',
        'role_id',
        'timezone',
        'state',
        'stripe_id',
        'card_brand',
        'card_last_four',
        'tiral_ends_at',
        'domain_activated',
        'activated_date',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = ['activated_date', 'trial_ends_at'];

    public function scopeEmail($query, $email)
    {
        return $query->where('email', $email);
    }
    
    public function role()
    {
        return $this->belongsTo('App\FuseRole');
    }
    
    public function isAdmin()
    {
        if ($this->role->name == 'Superadmin') {
            return true;
        }
        return false;
    }

    public function userSubscription()
    {
        return $this->hasOne('App\UserSubscription');
    }

    public function billing()
    {
        return $this->hasOne('App\UserStripeBilling');
    }

    public function getMonthlySubscriptionExpiredAttribute()
    {
        if ($this->userSubscription) {
            if ($this->userSubscription->plan->name === 'Free' || $this->userSubscription->userSubscription_expiry_date <= Carbon::now()) {
                return false;
            }
        }
        return true;
    }
    
    public function usersIsAccounts()
    {
        return $this->hasOne('App\InfsAccount', 'user_id');
    }
    
    public function infsAccounts()
    {
        return $this->hasMany('App\InfsAccount', 'user_id');
    }

    public function hasToken() {

        $sub_latest = $this->userSubscription->latest()->first();

        if($sub_latest->token_count > 0) {
            return true;
        }
        return false;
    }

    public function tokenCount() {

        $sub_latest = $this->userSubscription->latest()->first();
        return $sub_latest->token_count;
    }
    
    public function accountLimit()
    {
        $limit = isset($this->userSubscription->plan->infs_account_limit) ? $this->userSubscription->plan->infs_account_limit : 1;
        $accounts = count($this->infsAccounts);
        
        if ($limit <= $accounts) {
            return false;
        }
        return true;
    }
    
    public function userDocUsage()
    {
        return $this->hasOne('App\UserUsageDocs', 'user_id');
    }

    public function accounts() {
        return $this->hasMany('App\Accounts', 'owner_id');
    }
    
    public function getUserUsageCountAttribute()
    {
        if (isset($this->userDocUsage->docs_sent)) {
            return $this->userDocUsage->docs_sent;
        } else {
            return 0;
        }
    }
    
    public function getFullNameAttribute()
    {
        return "{$this->userAddress->first_name} {$this->userAddress->last_name}";
    }
    
    public function xeroAccount()
    {
        return $this->hasMany('App\XeroAccounts', 'user_id');
    }
    
    public function getdocumentLimitAttribute()
    {
        $docLimit = $this->userSubscription->plan->monthly_doc_limit;
        $userDocs = $this->userUsageCount;
        if ($docLimit <= $userDocs) {
            return false;
        }
        return true;
    }
    
    public function userAddress()
    {
        return $this->hasOne('App\UserAddress', 'user_id');
    }
    

    public function docuSign()
    {
        return $this->hasOne('App\DocsAccountsDocsign', 'user_id');
    }
    
    public function getDocuSignTokenAttribute()
    {
        if (isset($this->docuSign->access_token)) {
            return $this->docuSign->access_token;
        }
    }

}
