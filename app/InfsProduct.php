<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsProduct extends Model
{
    protected $table = 'infs_products';

    public $timestamps = true;

    protected $fillable = [
        'product_id',
    ];

}
