<?php 

// Docs feature
$domain = config('subdomains.docs').config('session.domain');

Route::group([ 'domain' => $domain, 'middleware' => 'auth' ], function () {
    Route::get('/', 'DocsController@redirectdashboard');
    Route::get('/dashboard', 'DocsController@index');
    Route::get('pandadocs', 'DocsController@pandadocs');
    Route::get('pandadocs/setupguide', 'DocsController@setupViewPandadocs');
    Route::get('createtag', 'InfusionSoftAuthController@createCategory');
    Route::get('docusign', 'DocsController@docusign');
    Route::get('history', 'DocsController@listUserDocHistory');
    Route::get('notifications', 'DocsController@listPandaUserNotifications');
    Route::get('manageaccounts', 'ManageAccountsController@listUsersAccount');
    Route::get('support', 'HomeController@supportView');

    Route::post('pandawebhook', 'WebhookController@processPandaWebhook');
    Route::post('docusignwebhook', 'WebhookController@processDocusignWebhook');
    Route::post('pandadocs/gettemplatedetails', 'DocsController@getTemplateDetailsPandadocs');
    Route::post('docusign/gettemplatedetails/', 'DocsController@getTemplateDetailsDocusign');
    Route::post('savetagselections', 'DocsController@saveTagSelections');
    Route::post('gettagsfromisaccount', 'DocsController@getTagsFromISAccount');
    Route::post('saveadditionaloptions', 'DocsController@saveAdditionalOptions');

    Route::get('get_opportunity_by_products', 'DocsController@getOpportunityByProducts');
    Route::post('delete-notty', 'DocsController@deletePandaUserNotifications');
    Route::post('listtemplates', 'DocsController@listTemplates');
    Route::post('save-template-settings', 'DocsController@saveTemplateSettings');
    Route::post('pandadocs/savetemplatesettings', 'DocsController@saveTemplateSettingsPandadocs');
});
