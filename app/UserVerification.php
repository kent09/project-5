<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserVerification extends Model
{
    protected $fillable = [
        'user_id', 'code'
    ];
}
