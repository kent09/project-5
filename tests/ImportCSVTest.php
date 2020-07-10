<?php

use App\User;
use App\InfsAccount;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImportCSVTest extends TestCase
{
    /**
     * Storage dependency to be injected to IoC Container
     * @var
     */
    protected $storage;
 
    /**
     * File dependency to be injected to IoC Container
     * @var
     */
    protected $file;
 
    /**
     * File System dependency to be injected to IoC Container
     * @var
     */
    protected $fileSystem;
 
    /**
     * Initial setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        
        //Setup mocks
        $this->storage = $this->mock('Illuminate\Contracts\Filesystem\Factory');
        $this->file = $this->mock('Illuminate\Contracts\Filesystem');
        $this->fileSystem = $this->mock('Illuminate\Filesystem\Filesystem');
    }

    /**
     * Mock Container dependencies
     *
     * @param string $class Class to mock
     *
     * @return void
     */
    public function mock($class)
    {
        $mock = Mockery::mock($class);
        $this->app->instance($class, $mock);
        return $mock;
    }

    public function test_it_verifies_that_the_page_is_working()
    {
        $this->signIn(User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first());

        $response = $this->call('GET', 'tools/csvimport');

        $this->assertEquals(200, $response->status());
    }

    public function test_it_verifies_that_the_first_step_page_is_working()
    {
        $this->signIn(User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first());

        $response = $this->call('GET', 'tools/csvimport/step1');
        
        $this->assertEquals(200, $response->status());
    }

    public function test_it_verifies_that_the_edit_page_is_working()
    {
        $this->signIn(User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first());

        $id = 6;

        $response = $this->call('GET', 'tools/csvimport/step1/' . $id);
        
        $this->assertEquals(200, $response->status());
    }

    public function test_step_1_invalid_file_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $sample_file    = __DIR__.'/import/invalid_file.jpg';

        $name           = str_random(8).'.jpg';

        $path           = sys_get_temp_dir().'/'.$name;

        copy($sample_file, $path);

        $uploadedFile = Mockery::mock(
            'Illuminate\Http\UploadedFile',
            [
                'getClientOriginalName'  => $name,
                'getClientOriginalExtension' => 'jpg',
                'getPath' => $path,
                'isValid' => true,
                'guessExtension' => 'jpg',
                'getRealPath' => null,
            ]
        );

        $data     = ['account' => $account_id, 'import_title' => 'Test 321'];

        $file     = ['csv_file' => $uploadedFile];

        $response = $this->call('POST', 'tools/csvimport/step2', [], $data, $file);

        $this->assertEquals(302, $response->status()); // This will redirect to step 1 with error notification
    }

    public function test_step_1_file_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $sample_file 	= __DIR__.'/import/test.csv';

        $name 			= str_random(8).'.csv';

        $path 			= sys_get_temp_dir().'/'.$name;

        copy($sample_file, $path);

        $uploadedFile = Mockery::mock(
            'Illuminate\Http\UploadedFile',
            [
                'getClientOriginalName'  => $name,
                'getClientOriginalExtension' => 'csv',
                'getPath' => $path,
                'isValid' => true,
                'guessExtension' => 'csv',
                'getRealPath' => null,
            ]
        );

        $data     = ['account' => $account_id, 'import_title' => 'Test 321'];

        $file     = ['csv_file' => $uploadedFile];

        $response = $this->call('POST', 'tools/csvimport/step2', [], $data, $file);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 2
    }

    public function test_step_2_file_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'csv_fields' => ['contacts_fields_f_loyalty_no','contacts_fields_f_email','contacts_fields_f_mobile','contacts_type','company_fields_f_company','company_type','orders_fields_order_id','orders_type','orders_settings_split_sku','orders_settings_split_name','orders_settings_split_qty','orders_settings_split_price','orders_settings_splittype','products_fields_sku','products_fields_items','products_type'],

            'infusionsoft_fields' => ['TrekkSoftID','Email','Phone1',0,0,0,0,0,0,0,0,0,0,0,0,0],
            'company_fields' => ['contacts_fields_f_loyalty_no','contacts_fields_f_email','contacts_fields_f_mobile','contacts_type','company_fields_f_company','company_type','orders_fields_order_id','orders_type','orders_settings_split_sku','orders_settings_split_name','orders_settings_split_qty','orders_settings_split_price','orders_settings_splittype','products_fields_sku','products_fields_items','products_type'],
            'comp_infusionsoft_fields' => [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0],
            'order_fields' => ['sku','product_name','qty','price'],
            'order_infusionsoft_fields' => [0,0,0,0]
        ];
        

        $response = $this->call('POST', 'tools/csvimport/step3', $data);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 3
    }

    public function test_step_3_file_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'filter_contact' => 'both',
            'match_contact_csv' => ['contacts_fields_f_loyalty_no', 'contacts_fields_f_email'],
            'match_contact_infs' => ['AccountCustomerNumber', 'Email'],
        ];

        $response = $this->call('POST', 'tools/csvimport/step4', $data);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 4
    }


    public function test_add_tags()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'apply_tags' => 'yes',
            'tags' => '93,92',
        ];

        $response = $this->call('POST', 'tools/csvimport/step5', $data);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 5
    }


    public function test_without_tags()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'apply_tags' => 'no',
            'tags' => '',
        ];

        $response = $this->call('POST', 'tools/csvimport/step5', $data);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 5
    }

    public function test_add_tags_with_empty_tags_field_intentionally()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'apply_tags' => 'yes',
            'tags' => '',
        ];

        $response = $this->call('POST', 'tools/csvimport/step5', $data);

        $this->assertEquals(302, $response->status()); // This will redirect to Step 5 with error notification
    }


    public function test_edit_csv_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $csv_id = 8;

        $response = $this->call('GET', 'tools/csvimport/step1/' . $csv_id);

        $this->assertEquals(200, $response->status()); // Redirect to edit page
    }

    public function test_edit_step_2_csv_upload()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $csv = \App\CsvImports::where('id', 8)->first();

        $path = base_path() . '/public/assets/uploads/csv_files/' . $csv->csv_file;

        $uploadedFile = Mockery::mock(
            'Illuminate\Http\UploadedFile',
            [
                'getClientOriginalName'  => $csv->csv_file,
                'getClientOriginalExtension' => 'csv',
                'getPath' => $path,
                'isValid' => true,
                'guessExtension' => 'csv',
                'getRealPath' => null,
            ]
        );
        
        $file     = ['csv_file' => $uploadedFile];

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'account' => $account_id->id,
            'import_title' => 'Test Jerson',
            'parm' => 'back'
        ];

        $response = $this->call('POST', 'tools/csvimport/step2', [], $data, $file);

        $this->assertEquals(302, $response->status()); // Redirect
    }

    public function test_delete_csv_file()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);


        $id 		= $user->infsAccounts()->first()->id;

        $response 	= $this->call('GET', 'tools/csvimport/delete/' . $id);

        $this->assertResponseOK();
    }


    public function test_apply_settings()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'import_account' => 1,
        ];

        $response = $this->call('POST', 'tools/csvimport/applysettings', $data);

        $this->assertEquals(200, $response->status());
    }

    /**
     * Test upload without files
     *
     * @return void
     */
    public function testNoFile()
    {
        $response = $this->call('POST', '/tools/csvimport/step2', [], []);
        $this->assertEquals('302', $response->status()); // Redirected with error notification
    }
}
