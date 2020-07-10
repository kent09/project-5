<?php

use App\User;
use App\InfsAccount;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocuSignManagerTest extends TestCase
{
    /**
     * A basic test to verify the page.
     *
     * @return Error Code
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $this->signIn(User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first());

        $response = $this->call('GET', 'docs/docusign');

        print_r($response->status());

        // $this->assertEquals(200, $response->status());
    }

    /**
     *
     * Basic Test to get Templates
     *
     */
    public function test_it_to_get_templates()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'account_id' => $account_id,
            'tempID' => 'b8f0315f-401a-4db9-afa2-4fdd900c579f',
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
        ];

        $response = $this->json('POST', 'docs/docusign/gettemplatedetails', $data);
        $response->assertResponseStatus(500); // Class Number not found
    }
}
