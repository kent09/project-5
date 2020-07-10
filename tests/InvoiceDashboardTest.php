<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InvoiceDashboardTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/invoices/dashboard');

        $this->assertEquals(200, $response->status());
    }
}
