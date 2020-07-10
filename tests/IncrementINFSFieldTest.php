<?php

use App\InfsAccount;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class IncrementINFSFieldTest extends TestCase
{


    /**
     * A basic test to verify the page.
     *
     * @return Error Code
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/scripts/addtovalues');

        $this->assertEquals(200, $response->status());
    }


    /**
     * A basic test add INFS Field with Auth.
     *
     * @return void
     */
    public function testAddINFSFieldWithMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);
        $infs = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'mode' => 'increment_field',
            'FuseKey' => $user->FuseKey,
            'app' => $infs->name,
            'contactID' => '~Contact.ID~',
            'fieldto' => 'Contact._WeeksNo',
            'amount' => 1,
        ];

        $response = $this->json('POST', '/scripts', $data);
        $response->assertResponseStatus(200);
    }




    /**
     * A basic test subtract INFS Field with Auth.
     *
     * @return void
     */
    public function testSubtractINFSFieldWithMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);
        $infs = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'mode' => 'increment_field',
            'FuseKey' => $user->FuseKey,
            'app' => $infs->name,
            'contactID' => '~Contact.ID~',
            'fieldto' => 'Contact._WeeksNo',
            'amount' => -1,
        ];

        $response = $this->json('POST', '/scripts', $data);
        $response->assertResponseStatus(200);
    }
}
