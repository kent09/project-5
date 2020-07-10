<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsOrder extends Model
{
    protected $table = 'infs_orders';

    public $timestamps = true;

    protected $fillable = [
        'order_id',
    ];

}
