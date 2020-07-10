<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocsWebhookLog extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'status','error_message','user_id','infs_account_id','completed_id','document_status','contactId','tag_applied', 'data', 'type'];
    protected $table = "docs_webhook_log";

    /**
    * Set status to negative indicating error happened and the message
    *
    * @return boolean if successful or not
    */
    public function logError($msg = "")
    {
        $this->error_message = $msg;
        $this->status = -1;
        return $this->save();
    }
}
