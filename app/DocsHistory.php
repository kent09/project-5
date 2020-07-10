<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DocsHistory extends Model
{
    protected $table = 'docs_webhook_log';
    
    //
    protected $fillable = [
        'user_id',
        'document_id',
        'document_status',
        'contactId',
    ];

    public function save(array $options = array())
    {
        parent::save($options);

        return $this;
    }
    
    public function infsAccount()
    {
        return $this->hasOne('App\InfsAccount', 'id', 'infs_account_id');
    }
}
