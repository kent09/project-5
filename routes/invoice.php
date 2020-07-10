<?php

// Invoices feature
$domain = config('subdomains.invoice').config('session.domain');

Route::group([ 'domain' => $domain, 'middleware' => 'auth' ], function () {
    Route::get('/', 'InvoiceController@redirectdashboard');
    Route::get('/dashboard', 'InvoiceController@index');
    Route::get('/xeroaccount', 'XeroInfusionsoftController@getXeroAccount');
    Route::get('support', 'HomeController@supportView');
    Route::get('xeroauth', 'XeroInfusionsoftController@authXero');

    Route::post('addxeroaccount', 'XeroInfusionsoftController@addXeroAccount');
    Route::post('deletexeroaccount', 'XeroInfusionsoftController@deleteXeroAccount');
    
    Route::get('xero_accounts', 'XeroInfusionsoftController@getAccounts');
    
    Route::get('/xero_invoice_copy', 'XeroInfusionsoftController@processOrderIntoINFS');
    Route::get('/xero_invoice_cron_sync', 'XeroInfusionsoftController@processOrderIntoINFSCron');
    Route::get('scripts/xero-invoice-copy', 'XeroInfusionsoftController@returnXeroInvoiceCopy');
    Route::get('scripts/xero-invoice-cron', 'XeroInfusionsoftController@returnXeroInvoiceCron');

    Route::post('/save-xero-invoice', 'InfusionSoftController@saveXeroInvoice');
    
    Route::post('/xero-cron-partial', 'InfusionSoftController@xeroCronPartial');
    Route::post('/save-xero-cron', 'InfusionSoftController@saveXeroCron');
});