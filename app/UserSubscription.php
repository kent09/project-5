<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSubscription extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'trial_ends_at', 'ends_at', 'subscription_renew_date'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'user_plan_id',
        'subscription_id', 
        'status' ,
        'subscription_renew_date',
        'user_product_id', 
        'ends_at',
        'customer_stripe_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected $table = "subscriptions";

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function plan()
    {
        return $this->belongsTo('App\Plans', 'user_plan_id');
    }
    
    public function product()
    {
        return $this->belongsTo('App\FusePlanProduct', 'user_product_id');
    }

    public function usage_log() {
        return $this->hasMany('App\UsageLog');
    }

}
