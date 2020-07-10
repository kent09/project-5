<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\InfsAccount;
use Illuminate\Support\Facades\Session;

class BulkContactTaggingTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }


    public function testContactFieldWithLogin()
    {
        Session::start();
        $user = User::where('email', 'ted@fusedsoftware.com')->first();
        $this->signIn($user);
        $this->actingAs($user);

        $infs = InfsAccount::where('name', 'zl328')->first();

        $data = [
            'infs_account_id' => $infs->id,
            '_token' => csrf_token()
        ];

        $response = $this->call('POST', '/tag/fields', $data);
        $this->assertEquals(200, $response->status());
    }

    public function testContactBulkTagging()
    {
        Session::start();
        $user = User::where('email', 'ted@fusedsoftware.com')->first();
        $this->signIn($user);
        $this->actingAs($user);

        //$infs = InfsAccount::where('name','zl328')->first();

        $data = [
            'qfield' => 'CompanyID',
            'listOfData' => '8726',
            'tags' => [ 135, 145 ],
            '_token' => csrf_token()
        ];

        $response = $this->call('POST', '/tag/contact/bulk', $data);
        $this->assertEquals(302, $response->status());
    }
}
