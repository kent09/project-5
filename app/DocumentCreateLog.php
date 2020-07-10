<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCreateLog extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'status','error_message','app','data'];
    protected $table = "document_create_logs";

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
