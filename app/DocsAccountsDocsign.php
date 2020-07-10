<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DocsAccountsDocsign extends Model
{
    protected $table = 'docs_accounts_docsign';
    protected $fillable = ['user_id','account_id','access_token','refresh_token','expires_date'];

    public function user()
    {
        return $this->belongsTo('User');
    }
}
