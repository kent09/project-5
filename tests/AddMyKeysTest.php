<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AddMyKeysTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_add_my_keys()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account = \App\InfsAccount::where('id', 36)->first();

        $data = [
            'id' => $account->id,
            'client_id' => '',
            'client_secret' => '',
            '_token' => 'ydy99dnAmQiovaEKsk3zoXUvCwuFkrxBHGFVHGBM',
        ];

        $response = $this->json('POST', '/manageaccounts/add-own-client-id-and-secret', $data);
        $response->assertResponseStatus(200);
    }
}
