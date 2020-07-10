<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocsCompleted extends Model
{
    protected $table = 'docs_completed';
    
    public $timestamps = true;
    
    protected $fillable = ['user_id','infs_account_id','document_id','template_id','http_post_log','contactId', 'type'];
}
