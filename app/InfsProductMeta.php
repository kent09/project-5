<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsProductMeta extends Model
{
    protected $table = 'infs_products_meta';

    public $timestamps = true;

    protected $fillable = [
        'product_id',
        'field_name',
        'field_value'
    ];

}
