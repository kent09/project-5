<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CountryFallbackOwner extends Model
{
    protected $table = "country_fallback_owners";

    protected $fillable = [
        'fallback_owner_id',
        'fallback_owner_name',
        'user_id',
        'infs_account_id',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function infusionsoft_account()
    {
        return $this->hasOne(InfsAccount::class, 'id', 'infs_account_id');
    }
}
