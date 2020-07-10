<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DocsAccountsPanda extends Model
{
    protected $table = 'docs_accounts_panda';
    protected $fillable = ['user_id','access_token','refresh_token','expire_date','active','api_key'];

    public function getExpiresInAttribute()
    {
        $now = Carbon::now();

        //diffInSeconds always returns positive value
        return $this->expire_date < $now ? -1 : $now->diffInSeconds($this->expire_date);
    }

    public function user()
    {
        return $this->belongsTo('User');
    }
}
