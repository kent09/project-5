<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FusePlanProduct extends Model
{
    protected $table = 'fuse_plans_products';
    
    //
    protected $fillable = [
        'plan_id',
        'stripe_product_id',
        'stripe_plan_id',
        'charge',
        'charge_freq'
    ];
    
    public function plan()
    {
        return $this->belongsTo('App\FusePlans', 'plan_id');
    }
}
