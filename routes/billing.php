<?php 

// Billing, User and INFS script routes
$domain = config('subdomains.account').config('session.domain');

Route::group([ 'domain' => $domain, 'middleware' => ['auth'] ], function () {
    // Route::get('/dashboard', 'UserSubscriptionController@index');
    // Route::get('/billing', 'UserSubscriptionController@index');
    Route::get('/billing/confirm', 'UserSubscriptionController@confirmOrder');
    Route::get('/billing/success', 'UserSubscriptionController@successOrder');
    Route::get('/billing/failed', 'UserSubscriptionController@failedOrder');

    Route::post('/billing', 'UserSubscriptionController@store'); 
    Route::post('/billing/confirm', 'UserSubscriptionController@processOrder');
    Route::post('/billing/webhook', 'StripeWebhookController@webhook');
    Route::post('/changeplan', 'UserSubscriptionController@changePlan');
    Route::post('/changeplan/confirm', 'UserSubscriptionController@processChangePlan');
    Route::post('/updatebillingaddress', 'UserSubscriptionController@updateBillingAddress');
    Route::post('/updatecard', 'UserSubscriptionController@updateCard');
    Route::get('/cancelsubscription', 'UserSubscriptionController@cancelSubscription');

    Route::get('/billing', 'UserSubscriptionController@index');
    Route::post('/stripe/webhook', 'StripeWebhookController@handleWebhook');

    Route::get('invoices/{id}', 'UserSubscriptionController@showInvoice');

});
