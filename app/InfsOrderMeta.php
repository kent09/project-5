<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsOrderMeta extends Model
{
    protected $table = 'infs_orders_meta';

    public $timestamps = true;

    protected $fillable = [
        'order_id',
        'field_name',
        'field_value'
    ];

}
