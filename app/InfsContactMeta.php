<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfsContactMeta extends Model
{
    protected $table = 'infs_contacts_meta';

    public $timestamps = true;

    protected $fillable = [
        'contact_id',
        'field_name',
        'field_value'
    ];

}
