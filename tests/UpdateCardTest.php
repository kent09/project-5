<?php


use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UpdateCardTest extends TestCase
{
    /**
     * A basic test for getting merchants without auth.
     *
     * @return void
     */
    public function testGetMerchantWithoutMiddleware()
    {
        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/get-stages', $data);
        $response->assertResponseStatus(401);
    }

    /**
     * A basic test for getting merchants with auth.
     *
     * @return void
     */
    public function testGetMerchantWithMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'accountID' => $account->id,
            '_token' => $account->access_token,
        ];

        $response = $this->json('POST', '/get-merchants', $data);
        $response->assertResponseStatus(200);
    }


    /**
     * A basic test for update cards with auth.
     *
     * @return void
     */

    public function testUpdateCardsWithMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);
        $infs = \App\InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'mode' => 'update_card',
            'FuseKey' => $user->FuseKey,
            'app' => $infs->name,
            'contactID' => '~Contact.ID~',
            'update_subscriptions' => 1,
            'rebill_subscriptions' => 0,
            'only_active' => 1,
            'rebill_orders' => 1,
            'mechant_id' => 12,
        ];

        $response = $this->call('POST', 'scripts', $data);
        $this->assertEquals(302, $response->status()); // Redirect to other page
    }


    /**
     * A basic test for update cards with auth.
     *
     * @return void
     */

    public function testUpdateCardsWithoutMiddleware()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $infs = \App\InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'mode' => 'update_card',
            'FuseKey' => $user->FuseKey,
            'app' => $infs->name,
            'contactID' => '~Contact.ID~',
            'update_subscriptions' => 1,
            'rebill_subscriptions' => 0,
            'only_active' => 1,
            'rebill_orders' => 1,
            'mechant_id' => 12,
        ];

        $response = $this->call('POST', 'scripts', $data);
        $this->assertEquals(500, $response->status()); // Error in backend
    }
}
