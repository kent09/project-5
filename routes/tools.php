<?php
// Tools feature
$domain = config('subdomains.tools').config('session.domain');

Route::group([ 'domain' => $domain, 'middleware' => 'auth' ], function () {
    Route::get('/', 'ToolsController@redirectdashboard');
    Route::get('dashboard', 'HomeController@index'); 
    Route::get('scripts/moveopportunities', 'ScriptController@moveOpportunities');
    Route::get('scripts/updatecreditcards', 'ScriptController@updateCreditCards');
    Route::get('scripts/addtovalues', 'ScriptController@addToValues');
    Route::get('scripts/namesfromorders', 'ScriptController@namesFromOrders');
    Route::get('scripts/copyvalues', 'ScriptController@copyValues');
    Route::get('scripts/calculatedates', 'ScriptController@calculateDates');
    Route::get('scripts/geo/postcodebasedowner', 'ScriptController@postcodeBasedOwner');
    Route::get('scripts/geo/countrybasedowner', 'ScriptController@countryBasedOwner');
    Route::get('scripts/geo/postcodecontacttagging', 'ScriptController@postcodeContactTagging');


//    Route::get('csvimport', 'ImportCSVController@index');
    Route::get('csvimport/delete/{id}', 'ImportCSVController@deleteImport');
//    Route::get('csvimport/cancel/{id}', 'ImportCSVController@cancelImport');
//    Route::post('csvimport/applysettings', 'ImportCSVController@applySettings');
//    Route::any('csvimport/step1/{parm?}', 'ImportCSVController@importStep1');
//    Route::any('csvimport/step2', 'ImportCSVController@importStep2');
//    Route::any('csvimport/step3', 'ImportCSVController@importStep3');
//    Route::any('csvimport/step4', 'ImportCSVController@importStep4');
//    Route::any('csvimport/step5', 'ImportCSVController@importStep5');
    Route::get('csvimport', 'CsvImportController@index');
    Route::post('csvimport/new-import', 'CsvImportController@newImport');
 //    Route::get('csvimport/step1', 'CsvImportController@showStep1');

    Route::group([ 'middleware' => ['state']], function () {
        Route::get('csvimport/step1/{id}', 'CsvImportController@step1')->name('step1');
        Route::get('csvimport/step2/{id}', 'CsvImportController@step2')->name('step2');
        Route::get('csvimport/step3/{id}', 'CsvImportController@step4')->name('step4'); // temporarily make as step 3
        Route::get('csvimport/step4/{id}', 'CsvImportController@step5')->name('step5'); // temporarily make as step 4
        Route::get('csvimport/step5/{id}', 'CsvImportController@step6')->name('step6'); // temporarily make as step 5
    });  

    Route::get('csvimport/start/{id}', 'CsvImportController@stepStart');
    Route::post('csvimport/step1/{id}', 'CsvImportController@postStep1')->name('postStep1');
    Route::post('csvimport/step2/{id}', 'CsvImportController@postStep2')->name('postStep2');
    Route::post('csvimport/step2/{id}/settings', 'CsvImportController@settingsStep2')->name('settingsStep2');

    Route::post('csvimport/change/session', 'CsvImportController@changeSession');

     // temporarily make as step 3
    Route::post('csvimport/step3/{id}', 'CsvImportController@postStep4')->name('postStep4'); // temporarily make as step 3
     // temporarily make as step 4
    Route::post('csvimport/step4/{id}', 'CsvImportController@postStep5')->name('postStep5'); // temporarily make as step 4



    Route::get('sync/company/contact', 'SyncController@companyContactSync');
    Route::post('sync/subscribe', 'InfusionSoftScriptsController@syncSubscribe');
    Route::post('sync/unsubscribe', 'InfusionSoftScriptsController@syncUnsubscribe');
    Route::get('sync/{id}/config', 'SyncController@syncConfig');
    Route::post('sync/fields', 'SyncController@addField');
    Route::post('sync/fields/edit', 'SyncController@editField');
    Route::get('sync/fields/{id}/delete', 'SyncController@deleteField');

    Route::post('tag/fields', 'InfusionSoftScriptsController@getTagFields');
    Route::get('tag/contact', 'BulkContactTaggingController@index');
    Route::post('tag/contact/bulk', 'InfusionSoftScriptsController@contactBulkTagging');

    Route::post('sync/infs/fields', 'InfusionSoftScriptsController@getCustomFields');

    Route::get('/wp-plugins/gravity-infusionsoft-link', 'InfusionSoftGravityController@index');

    Route::post('csvimport-update-step', 'CsvImportController@updateStep');
});