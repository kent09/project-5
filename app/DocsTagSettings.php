<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocsTagSettings extends Model
{
    protected $table = "docs_tag_config";

    protected $fillable = [
        'user_id', 'document_status', 'applied_tag_id','template_id','infs_account_id'
    ];
}
