<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plans extends Model
{
    protected $fillable = [
        'label',
        'monthly_token_amount',
        'billing_period',
        'stripe_sub_id',
        'price',
        'affiliate_commission'
    ];

    public function subscription() {
        return $this->hasOnly('App\UserSubscription');
    }
}
