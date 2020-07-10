<?php

use App\InfsAccount;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MoveOpportunitiesTest extends TestCase
{
    /**
     * A basic test to setup a http post.
     *
     * @return void
     */
    public function test_verifies_that_the_page_is_working()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $response = $this->call('GET', '/scripts/moveopportunities');
        $this->assertEquals(200, $response->status());
    }


    public function test_add_new_account()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        
        $this->signIn($user);

        $response = $this->json('GET', '/manageaccounts/add');

        $response->assertResponseStatus(302); // Redirect to Integrations Panel
    }


    /**
     * A basic test to setup a http post.
     *
     * @return void
     */
    public function testHttpPost()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $infs = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'mode' => 'move_stages',
            'FuseKey' => $user->FuseKey,
            'app' => $infs->name,
            'contactID' => '~Contact.ID~',
            'stageid' => 37,
        ];

        $response = $this->json('POST', 'scripts', $data);
        $response->assertResponseStatus(500); // There's an error on the backend
    }


    /**
     * A basic test for getting stages without auth.
     *
     * @return void
     */
    public function testGetStagesWithOutMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();

        $data = [
            'accountID' => $user->id,
            '_token' => $user->access_token,
        ];

        $response = $this->json('POST', '/get-stages', $data);
        $response->assertResponseStatus(401);
    }

    /**
     * A basic test for getting stages with auth.
     *
     * @return void
     */
    public function testGetStagesWithMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $data = [
            'accountID' => $user->id,
            '_token' => $user->access_token,
        ];

        $response = $this->json('POST', '/get-stages', $data);
        $response->assertResponseStatus(200);
    }
}
