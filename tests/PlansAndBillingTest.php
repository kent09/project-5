<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlansAndBillingTest extends TestCase
{

    /**
     *
     * Basic Test to verify that the page is working
     *
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/billing');

        $this->assertEquals(200, $response->status());
    }


    public function test_update_billing_address()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token'        => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'first_name'    => 'test',
            'last_name'     => 'dev',
            'company_name'  => 'test',
            'email'     => $user->email,
            'phone'     => '99',
            'address1'  => 'Lorem ipsum',
            'address2'  => '',
            'city'      => 'test',
            'country'   => 'India',
            'state'     => 'QLD',
            'post_code' => '300001'
        ];

        $response = $this->json('POST', '/updatebillingaddress', $data);
        $response->assertResponseStatus(302); // Redirect to Billing Page with notification
    }


    public function test_change_plan()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'Package' => 1
        ];

        $response = $this->json('POST', 'changeplan', $data);
        $response->assertResponseStatus(302); // Redirect to Billing COnfirmation Page
    }

    public function test_confirm_plan()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'Package' => 1
        ];

        $response = $this->json('POST', 'changeplan/confirm', $data);
        $response->assertResponseStatus(302); // Redirect to another page
    }
}
