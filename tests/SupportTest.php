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

        $response = $this->call('GET', '/support');
        $this->assertEquals(200, $response->status());
    }

    /**
     *
     * @return The status code is 422 - Back-end Error
     *
     */
    public function test_it_to_submit_a_support_message_with_authenticated_user()
    {
        $user = App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('user_id', $user->id)->first();
        $faker = Faker\Factory::create();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
            'name' => $user->first_name. ' ' . $user->last_name,
            'email' => $user->email,
            'type' => 'Sales',
            'phone' => '',
            'message' => $faker->sentence(200),
        ];

        $response = $this->call('POST', '/support', $data);
        $this->assertEquals(302, $response->status()); // Redirected with success notification
    }


    public function test_it_to_submit_a_support_message_with_unauthenticated_user()
    {
        $user = App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('user_id', $user->id)->first();
        $faker = Faker\Factory::create();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
            'name' => $user->first_name. ' ' . $user->last_name,
            'email' => $user->email,
            'type' => 'Sales',
            'phone' => '',
            'message' => $faker->sentence(200),
        ];

        $response = $this->call('POST', '/support', $data);
        $this->assertEquals(302, $response->status()); // Redirected to login page
    }
}
