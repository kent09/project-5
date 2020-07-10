<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FuseInfsProducts extends Model
{
    protected $table = 'fuse_infs_products';
    
    //
    protected $fillable = [
        'plan_id',
        'infs_product_id',
        'infs_sub_id',
        'charge',
        'charge_freq'
    ];
    
    public function plan()
    {
        return $this->belongsTo('App\FusePlans', 'plan_id');
    }
}
