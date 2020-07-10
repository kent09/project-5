<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CalculateAndStoreDateTest extends TestCase
{
    public function test_it_verifies_that_the_page_is_working()
    {
        $this->signIn(User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first());

        $response = $this->call('GET', '/scripts/calculatedates');

        $this->assertEquals(200, $response->status());
    }


    /**
     *
     * Test the date calculation
     *
     * @return Internal Server Error
     */
    public function test_it_to_calculate_date()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'mode' 		=> 'calculate_date',
            'FuseKey' 	=> $user->FuseKey,
            'app' 		=> $user->infsAccounts()->first()->name,
            'contactID' => '~Contact.ID~',
            'fieldto' 	=> 'Contact._CustomField2',
            'startdate' => 'today',
            'add_time' 	=> '14days',
        ];

        $response = $this->call('POST', '/scripts', $data);

        $this->assertEquals(500, $response->status());
    }

    /**
     *
     * Test the date calculation from the application
     *
     * @return Success
     */
    public function test_it_to_calculate_date_for_testing_values()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' 	=> $user->infsAccounts()->first()->access_token,
            'startDate' => 'today',
            'addTime' 	=> '14days',
        ];

        $response = $this->json('POST', '/get-dates', $data);
        $response->assertResponseStatus(200);
    }
}
