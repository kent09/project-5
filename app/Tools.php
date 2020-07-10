<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tools extends Model
{
    protected $fillable = [
            'code',
            'tool_name',
            'tool_url'
    ];
}
