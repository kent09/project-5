<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CountryOwner extends Model
{
    protected $table = 'country_owner';

    protected $fillable = [
        'user_id',
        'infs_account_id',
        'status',
        'owner_name',
        'infs_person_id',
    ];
    
    public function country_owner_groups()
    {
        return $this->hasMany(CountryOwnerGroup::class, 'country_owner_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function infusionsoft_account()
    {
        return $this->hasOne(InfsAccount::class, 'id', 'infs_account_id');
    }
}
