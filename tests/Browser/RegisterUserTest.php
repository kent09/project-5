<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RegisterUserTest extends TestCase
{
    /**
     * A basic test for user registration.
     *
     * @return void
     */
    public function testRegisterANewUser()
    {
        $faker = Faker\Factory::create();

        $this->visit('/register')
         ->type($faker->firstName, 'first_name')
         ->type($faker->lastName, 'last_name')
         ->type($faker->company, 'company_name')
         ->type($faker->email, 'email')
         ->type($faker->phoneNumber, 'phone')
         ->select('Asia/Singapore', 'timezone')
         ->type($faker->country, 'country')
         ->type($faker->password, 'password')
         ->type($faker->password, 'password_confirmation')
         ->press('Confirm Your Email')
         ->seePageIs('/register');
    }
}
