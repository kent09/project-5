<?php

use App\User;
use App\InfsAccount;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CopyOrderToXeroTest extends TestCase
{
    /**
     * A basic test to verify that the page is working.
     *
     * @return void
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', 'invoices/scripts/xero-invoice-cron');

        $this->assertEquals(200, $response->status());
    }

    public function test_it_will_add_infs_account()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->json('GET', '/manageaccounts/add');

        $response->assertResponseStatus(302); // Redirected to FusedTools Account Integration
    }

    public function test_it_get_xero_cron_partial()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'accountID' => $account_id->id,
            'xeroID' => 29,
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
        ];

        $response = $this->json('POST', 'xero-cron-partial', $data);
        $response->assertResponseStatus(404); // The page is not found
    }

    public function test_it_add_xero_account()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $response = $this->json('GET', 'xero-account');
        
        $response->assertResponseStatus(404); // The page is not found
    }
}
