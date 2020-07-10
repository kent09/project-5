<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\InfsAccount;

class CompanyContactSyncTest extends TestCase
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

    public function testAddNewFieldWithoutMiddleware()
    {
        $id = InfsAccount::first()->id;

        $data = [
            'account' => $id,
            'cfield' => 'Company',
            'ctfield' => '_TestCompanyName'
        ];

        $response = $this->json('POST', '/sync/fields', $data);
        $response->assertResponseStatus(401);
    }

    public function testAddNewFieldWithMiddleware()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $id = InfsAccount::first()->id;

        $data = [
            'account' => $id,
            'cfield' => 'Company',
            'ctfield' => '_TestCompanyName'
        ];

        $response = $this->json('POST', '/sync/fields', $data);
        $response->assertResponseStatus(302); // redirection back to the sync dashboard page
    }

    public function testCompanyContactSync()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $infs = $user->infsAccounts()->first();

        $data = [
            "event_key"=>"contact.edit",
            "object_type"=>"contact",
            "object_keys"=>[["apiUrl"=>"", "id"=>33891, "timestamp"=>"2018-12-19T01:00:16Z"]],
            "api_url"=>""
        ];

        $response = $this->json('POST', 'sync/'.$infs->name, $data);
        $response->assertResponseStatus(200); // redirection back to the sync dashboard page
    }
}
