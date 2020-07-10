<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class XeroHttpPosts extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','infs_account_id','xero_account_id', 'contactId', 'xero_contact_id', 'post_data' ,'infs_order_id','infs_order_data','xero_invoice_id','status'
    ];

    protected $table = "xero_http_posts";
}
