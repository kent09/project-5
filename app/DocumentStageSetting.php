<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentStageSetting extends Model
{
    protected $fillable = [
        'user_id', 'temp_id', 'document_status', 'infs_opportunity_stage','created_at', 'updated_at', 'create_opp_if_not_exists'
    ];
}
