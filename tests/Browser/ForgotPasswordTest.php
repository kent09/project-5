<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ForgotPasswordTest extends TestCase
{
    public function test_it_verifies_that_pages_load_properly()
    {
        $this->visit('/login');
    }


    public function test_it_follows_links()
    {
        $this->visit('/login')
             ->click('Forgot your password?')
             ->seePageIs('/password/reset');
    }

    public function test_it_verifies_that_pages_load_properly_with_logged_in_user()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $this->visit('/changepassword');
    }

    public function test_it_follows_links_with_logged_in_user()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $this->visit('/changepassword')
             ->type('123456', 'password')
             ->type('123456', 'password_confirmation')
             ->click('Change')
             ->seePageIs('/changepassword');
    }
}
