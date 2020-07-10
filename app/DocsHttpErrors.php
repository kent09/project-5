<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocsHttpErrors extends Model
{
    protected $table = 'docs_http_errors';
    
    //
    protected $fillable =[
        'user_id',
        'infs_account_id',
        'error_type',
        'message',
        'role_data',
        'document_data',
        'template_id',
        'contactId',
        'status'
    ];
}
