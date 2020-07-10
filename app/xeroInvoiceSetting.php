<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class xeroInvoiceSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','account_id','xero_id', 'settings'
    ];

    protected $table = "xero_invoice_settings";
}
