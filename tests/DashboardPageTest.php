<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DashboardPageTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_to_verifies_the_tools_page_if_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', '/dashboard');
        $this->assertEquals(200, $response->status());
    }

    public function test_it_to_verifies_the_docs_page_if_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', 'docs/dashboard');
        $this->assertEquals(200, $response->status());
    }


    public function test_it_to_verifies_the_invoice_page_if_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', 'invoices/dashboard');
        $this->assertEquals(200, $response->status());
    }
}
