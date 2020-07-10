<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentAdditionalInfsFields extends Model
{
    protected $fillable = [
        'user_id', 'type', 'contact_field','temp_id', 'opportunity_field', 'created_at', 'updated_at'
    ];
}
