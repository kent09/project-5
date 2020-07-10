<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HTTPXeroCreatorTest extends TestCase
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

        $response = $this->call('GET', 'invoices/scripts/xero-invoice-copy');

        $this->assertEquals(200, $response->status());
    }

    public function test_it_trigger_the_script_and_assign_a_copy()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'mode' 			=> 'xero_invoice_copy',
            'FuseKey' 		=> $user->FuseKey,
            'app' 			=> $user->infsAccounts()->first()->name,
            'contactID' 	=> '~Contact.ID~',
            'SalesAccount' 	=> 'IE. 201',
            'InvoiceStatus' => 1,
            'TaxStatus' 	=> 0,
        ];

        $response = $this->json('POST', 'scripts', $data);
        $response->assertResponseStatus(500); // There is an error on the backend
    }
}
