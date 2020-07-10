<?php

namespace App;

use App\Repositories\InfsContactRepository;
use Illuminate\Database\Eloquent\Model;

class InfsContact extends Model
{
    protected $table = 'infs_contacts';

    public $timestamps = true;

    protected $fillable = [
        'contact_id',
    ];

    /**
     * Helpers
     */

    public function getInfsId() {
        return $this->contact_id;
    }

    public function getInfsIdName() {
        return 'contact_id';
    }

    public function getRepo() {
        return new InfsContactRepository;
    }

    /**
     * Relationships
     */
    public function metadata() {
        return $this->hasMany('App\InfsContactMeta', 'contact_id');
    }

}
