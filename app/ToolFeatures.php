<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ToolFeatures extends Model
{
    protected $fillable = [
        'tool',
        'enable',
        'description',
        'token_cost',
        'amount_per',
        'amount_unit'
    ];
    
}
