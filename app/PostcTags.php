<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PostcTags extends Model
{
    protected $table = 'postc_tags';
    
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'infs_account_id',
        'status',
        'postc_type',
        'postc_country_code',
        'postc_code',
        'postc_radius',
        'postc_units',
        'postc_list',
        'tag_id'
    ];

    public function infsAccount()
    {
        return $this->belongsTo('App\User', 'infs_account_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
