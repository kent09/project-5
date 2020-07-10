<?php

// Route::get('test-cron', 'BillingUsageController@resetAllowance');

// Route::auth();

Auth::routes();

// Superadmin routes
Route::group([ 'prefix' => 'superadmin', 'middleware' => ['auth', 'admin']], function () {
    Route::get('/', 'Admin\HomeController@index')->name('admin');
    Route::get('/users', 'Admin\UsersController@index');
});

Route::get('/showCode', 'SetupWizardController@showEnterCodePage');
Route::post('/resend-activation', 'SetupWizardController@resendActivation');
Route::post('/verifyCode', 'SetupWizardController@verifyCode');
Route::get('/activateDomain', 'DomainActivationController@activateDomain');
Route::get('/licenseCheck', 'DomainActivationController@licenseCheck');



Route::group([ 'middleware' => ['auth']], function () {
    Route::get('/connect/infusionsoft', 'InfusionSoftAuthController@connectInfusionSoft')->name('/connect/infusionsoft');
    Route::get('/infusionsoft/redirect', 'InfusionSoftAuthController@redirect');
    Route::get('/showConnect', 'SetupWizardController@renderConnectPage')->name('showConnect');
    Route::get('/fuseddocs', 'InfusionSoftAuthController@saveContact');
   
    Route::get('/account-settings', 'UserController@accountSetting');

    Route::post('/get-stages', 'InfusionSoftController@getAllStage');
    Route::post('/get-merchants', 'InfusionSoftController@getAllMerchants');
    Route::post('/get-dates', 'InfusionSoftController@calculateDate');
    Route::post('/postc-tags', 'InfusionSoftController@postCtags');
    Route::post('/post-code', 'InfusionSoftController@postCode');
    Route::post('/tag-contact', 'InfusionSoftController@tagContact');
    Route::post('/save-group', 'InfusionSoftController@saveGroup');
    Route::post('/post-owner', 'InfusionSoftController@postOwner');
    Route::post('/add-owner', 'InfusionSoftController@addOwner');
    Route::post('/edit-owner', 'InfusionSoftController@editOwner');
    Route::post('/edit-owner-group', 'InfusionSoftController@editOwnerGroup');
    Route::post('/update-owner', 'InfusionSoftController@updateOwner');
    Route::post('/delete-owner', 'InfusionSoftController@deleteOwner');
    Route::post('/delete-owner-group', 'InfusionSoftController@deleteOwnerGroup');
    Route::post('/radiusMap', 'InfusionSoftController@radiusMap');
    Route::post('/post-code-delete', 'InfusionSoftController@deletePostcodeTag');
    Route::post('/post-code-retag', 'InfusionSoftController@reApplyTag');
    Route::post('/infs-contact-fields', 'InfusionSoftController@infsContactFields');
    Route::post('/get-infusion-account', 'InfusionSoftController@getInfusionsoftAccount');

    Route::post('/account-settings', 'UserController@accountSettingUpdate');

    /**
     * Country Based Owner Post HTTP
     */
    Route::group(['prefix' => 'country-based-owner'], function () {
        Route::post('/post-owner', 'InfusionSoftController@postOwnerCBO');
        Route::post('/add-owner', 'InfusionSoftController@addOwnerCBO');
    });

    Route::group(['prefix' => 'api/v1'], function () {
        Route::group(['prefix' => 'user'], function () {
            Route::get('infusionsoft-accounts', 'UserInfusionsoftAccount@index');
        });

        Route::group(['prefix' => 'country-owner-groups'], function () {
            Route::post('get/by-user-infusionsoft-account-id', 'CountryOwner@getByUserInfusionsoftAccountId');
        });

        Route::group(['prefix' => 'country-owners'], function () {
            Route::get('/', 'CountryOwner@index');
            Route::delete('/{id}', 'CountryOwner@destroy');
        });

        Route::post('country-based-owner', 'CountryBasedOwner@handle');
        Route::patch('country-based-owner/{id}', 'CountryBasedOwner@patch');

        Route::resource('fallback-owner', 'CountryFallbackOwner', [
            'as' => 'fallback-owner',
            'only' => ['show', 'store', 'update']
        ]);
    });

    Route::get('/updateMyPlanCron', 'InfusionSoftAuthController@refreshTokenCron');
    Route::get('/test', 'TestController@index');
    Route::get('/deleteAreaRmvTag', 'TestController@deleteAreaRmvTag');

    // Route::get('/processOwnerAssignmentCron','InfusionSoftController@processOwnerAssignmentCron');

    Route::post('/reAssignContactOwner', 'InfusionSoftController@reAssignContactOwner');
    Route::post('/assignContactOwner', 'PostCodeOwnerController@assignContactOwner');
});

//James Added Docs Routes
Route::group([ 'middleware' => ['auth']], function () {
    Route::group([ 'prefix' => 'manage-documents', 'middleware' => ['infusionsoft']], function () {
        Route::get('/', 'DocsController@listUsersAccount');
        Route::get('/add', 'DocsController@addUsersAccount');
        Route::get('/save', 'DocsController@saveUsersAccount');
        Route::post('/change_status', 'DocsController@changeStatusOfAccount');
        Route::post('/delete', 'DocsController@deleteAccount');
    });
    
    Route::get('docs/save', 'DocsController@savePandaCredentials');
    Route::get('notty-count-sync', 'DocsController@SyncNotificationsCount');
    
    //Pandadoc
    Route::get('/docs/manage-panda-account', 'DocAuthPandaController@index');
    Route::get('/pandadoc/redirect', 'DocAuthPandaController@redirect');
    Route::get('/connect/pandadocs', 'DocAuthPandaController@connectPandaDoc')->name('/connect/pandadocs');
    
    //Docusign routes
    Route::get('connect/docusign', 'DocAuthDocusignController@connectDocusign');
    Route::get('connect/docusign/reauth', 'DocAuthDocusignController@refreshDocusignAccount');
    Route::get('docusign/redirect', 'DocAuthDocusignController@authDocusignAccount');

    Route::post('/connect/pandadocs/delete', 'DocAuthPandaController@deletePandadoc')->name('delete pandadoc');
    Route::post('/connect/docusign/delete', 'DocAuthDocusignController@deleteDocusign')->name('delete docusign');

    Route::get('/fetchLastlead', 'InfusionSoftAuthController@fetchLastleadDetails');
    Route::get('/test-stripe', 'StripeController@fetchAllCustomer');
});



/** Refactored code by Ted **/

//TOOLS MODULE...

Route::post('/changepassword', 'UserController@changePassword');
Route::post('scripts', 'InfusionSoftScriptsController@scripts')->name('scripts');
Route::post('/sync/{account}', 'InfusionSoftScriptsController@sync');

Route::get('/changepassword', 'UserController@changePasswordView');
Route::get('manageaccounts', 'ManageAccountsController@listUsersAccount');
Route::get('manageaccounts/add', 'ManageAccountsController@addUsersAccount');
Route::get('manageaccounts/save', 'ManageAccountsController@saveUsersAccount');
Route::get('manageaccounts/getname', 'ManageAccountsController@getAccountName');
Route::get('/support', 'HomeController@supportView');
Route::post('/support', 'HomeController@support');

Route::post('manageaccounts/reauthaccount', 'ManageAccountsController@reauthAccount');
Route::get('manageaccounts/regrant-permission', 'ManageAccountsController@grantNewPermission');
Route::post('manageaccounts/delete', 'ManageAccountsController@deleteAccount');
Route::post('manageaccounts/rename', 'ManageAccountsController@renameAccount');

Route::get('manageaccounts/get-client-and-secret-id', 'ManageAccountsController@getClientAndSecretId');
Route::post('manageaccounts/add-own-client-id-and-secret', 'ManageAccountsController@addOwnClientIdAndSecret');


Route::group([ 'domain' => env('FUSEDSUITE_APP_SUBDOMAIN').'.'.env('APP_URL'), 'middleware' => 'auth' ], function () {
    Route::get('/', 'AppController@index');
    Route::get('/dashboard', 'AppController@dashboard');
});



Route::group([ 'middleware' => 'auth' ], function () {
    Route::get('tools/panda/process/{id}', 'DocsPandaController@processCreateDocument');
    Route::get('tools/docusign/process/{id}', 'DocusignController@processCreateDocument');

    Route::post('tools/panda', 'DocsPandaController@createDocument');
    Route::post('tools/docusign', 'DocusignController@createDocument');
});




Route::group([ 'middleware' => ['auth', 'infusionsoft']], function () {
    Route::get('/', 'HomeController@homeredirect');
    Route::get('/home', 'HomeController@index')->name('home');
});