<?php

namespace App;

use App\Repositories\InfsCompanyRepository;
use Illuminate\Database\Eloquent\Model;

class InfsCompany extends Model
{
    protected $table = 'infs_companies';

    public $timestamps = true;

    protected $fillable = [
        'company_id',
    ];

    /**
     * Helpers
     */

    public function getInfsId() {
        return $this->company_id;
    }

    public function getInfsIdName() {
        return 'company_id';
    }

    public function getRepo() {
        return new InfsCompanyRepository;
    }

    /**
     * Relationships
     */
    public function metadata() {
        return $this->hasMany('App\InfsCompanyMeta', 'company_id');
    }
}
