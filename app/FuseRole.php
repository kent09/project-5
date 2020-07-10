<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FuseRole extends Model
{
    protected $table = 'fuse_roles';
    
    public $timestamps = true;
    
    protected $fillable = ['name'];
}
