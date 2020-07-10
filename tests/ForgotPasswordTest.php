<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ForgotPasswordTest extends TestCase
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

        $response = $this->call('GET', 'changepassword');

        $this->assertEquals(200, $response->status());
    }

    public function test_change_password()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'password' => '123456',
            'password_confirmation' => '123456',
        ];

        $response = $this->json('POST', 'changepassword', $data);
        $response->assertResponseStatus(302); // Will redirect to change password page with notification
    }
}
