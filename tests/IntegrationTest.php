<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class IntegrationTest extends TestCase
{
    public function test_it_verifies_that_the_pages_is_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', '/manageaccounts');
        $this->assertEquals(200, $response->status());
    }

    public function test_it_verifies_that_the_pages_is_working_for_unauthenticated_user()
    {
        $user = \App\User::first();

        $response = $this->call('GET', '/manageaccounts');
        $this->assertEquals(500, $response->status());
    }


    public function test_to_edit_infs_account()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'id' => $account->id,
        ];

        $response = $this->json('GET', '/manageaccounts/getname', $data);
        $response->assertResponseStatus(200);
    }


    public function test_to_save_infs_account()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'id' => $account->id,
            'name' => 'zl328',
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/manageaccounts/rename', $data);
        $response->assertResponseStatus(200);
    }


    public function test_to_reaunthenticate_infs_account()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/manageaccounts/reauthaccount', $data);
        $response->assertResponseStatus(200);
    }


    public function test_to_disconnect_infs_account()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/manageaccounts/delete', $data);
        $response->assertResponseStatus(200);
    }


    public function test_it_verifies_that_the_import_csv_page_is_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', '/csvimport');
        $this->assertEquals(200, $response->status());
    }

    
    public function test_it_to_disconnect_panda_doc_integration()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/connect/pandadocs/delete', $data);
        $response->assertResponseStatus(500);
    }

    public function test_it_to_connect_docu_sign_integration()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'response_type' => 'code',
            'scope' => 'signature',
            'client_id' => $account->client_id,
            'redirect_uri' => '',
        ];

        $response = $this->call('GET', '/oauth/auth', $data);
        $this->assertEquals(404, $response->status()); // The page is not found
    }
}
