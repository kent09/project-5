<?php


use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BrowserLoginTest extends TestCase
{
    /**
     * A basic test for clicking login link.
     *
     * @return void
     */
    public function testAlreadyAMemberLink()
    {
        $this->visit('/')
         ->click('Login')
         ->seePageIs('/login');
    }

    /**
     * A basic test for login Form.
     *
     * @return void
     */
    public function testUserLogin()
    {
        $this->visit('/login')
         ->type('ted@fusedsoftware.com', 'email')
         ->type('123456', 'password')
         ->check('remember')
         ->press('Login')
         ->seePageIs('/dashboard');
    }
}
