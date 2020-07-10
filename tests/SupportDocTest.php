<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SupportDocTest extends TestCase
{
    /**
      *
      * Basic Test to verify the page
      *
      */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', 'docs/support');

        $this->assertEquals(200, $response->status());
    }

    /**
     *
     * Basic Test to get the Document History
     *
     */
    public function test_to_send_a_support_ticket()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'name' =>  'Test User',
            'email' =>  $user->email,
            'type' =>  'Sales',
            'phone' =>  '09161235467',
            'message' =>  'unit test',
        ];

        $response = $this->call('POST', 'support', $data);
        
        $this->assertEquals(302, $response->status());
    }
}
