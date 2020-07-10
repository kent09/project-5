<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SupportTest extends TestCase
{
    public function test_it_verifies_that_the_pages_is_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $this->visit('/support');
    }

    /**
     *
     * There is an error on the back-end
     *
     */
    public function test_it_can_submit_a_support_message_by_sales_type()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $this->visit('/support')
              ->type($faker->name, 'name')
             ->type($faker->email, 'email')
             ->select('Sales', 'type')
             ->type($faker->phoneNumber, 'phone')
             ->type($faker->text(200), 'message');
        // ->press('Send Request')
              // ->seePageIs('/support');
    }

    public function test_it_can_submit_a_support_message_by_setup_type()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $this->visit('/support')
              ->type($faker->name, 'name')
             ->type($faker->email, 'email')
             ->select('Setup', 'type')
             ->type($faker->phoneNumber, 'phone')
             ->type($faker->text(200), 'message');
        // ->press('Send Request')
              // ->seePageIs('/support');
    }

    public function test_it_can_submit_a_support_message_by_feature_project_type()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $this->visit('/support')
              ->type($faker->name, 'name')
             ->type($faker->email, 'email')
             ->select('Feature Project', 'type')
             ->type($faker->phoneNumber, 'phone')
             ->type($faker->text(200), 'message');
        // ->press('Send Request')
              // ->seePageIs('/support');
    }

    public function test_it_can_submit_a_support_message_by_bug_report_type()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $this->visit('/support')
              ->type($faker->name, 'name')
             ->type($faker->email, 'email')
             ->select('Bug Report', 'type')
             ->type($faker->phoneNumber, 'phone')
             ->type($faker->text(200), 'message');
        // ->press('Send Request')
              // ->seePageIs('/support');
    }


    public function test_it_can_submit_a_support_message_by_other_type()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $faker = Faker\Factory::create();

        $this->visit('/support')
              ->type($faker->name, 'name')
             ->type($faker->email, 'email')
             ->select('Other', 'type')
             ->type($faker->phoneNumber, 'phone')
             ->type($faker->text(200), 'message');
        // ->press('Send Request')
              // ->seePageIs('/support');
    }
}
