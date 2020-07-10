<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FusePlans extends Model
{
    protected $table = 'fuse_plans';
    
    public $timestamps = true;
    
    protected $fillable = ['name','monthly_task_limit','daily_record_limit', 'monthly_doc_limit','infs_account_limit'];
    
    public function product()
    {
        return $this->hasMany('App\FusePlanProduct', 'plan_id');
    }
}
