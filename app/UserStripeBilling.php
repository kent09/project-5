<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserStripeBilling extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','stripe_id', 'card_brand', 'card_last_four'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    protected $table = "user_stripe_billing";
}
