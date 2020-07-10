<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BillingFeatureTest extends TestCase
{
    public function testBillingIndexNoLogin()
    {
        $response = $this->json('GET', '/billing');
        $response->assertResponseStatus(401);
    }

    public function testBillingIndexLogin()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $response = $this->get('/billing');
        $response->assertViewHas('user');
    }

    public function testConfirmOrder()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $this->addSession(['product_id' => 3524, 'stripe_token'=> 'MhvbcIOdL4AOeuywgSEi9Tsh']);
        $response = $this->get('/billing/confirm');
        $response->assertResponseStatus(500);
    }

    public function testSuccessOrderRedirectToBilling()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $response = $this->get('/billing/success');
        $response->assertRedirectedTo('/billing');
    }

    public function testSuccessOrderRedirect()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $this->addSession(['product_id' => 3524, 'stripe_token'=> 'MhvbcIOdL4AOeuywgSEi9Tsh']);

        $response = $this->get('/billing/success');
        $response->assertResponseStatus(500);
    }

    public function testFailedBillingRedirect()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $response = $this->get('/billing/failed');
        $response->assertRedirectedTo('/billing');
    }

    public function testFailedBilling()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $this->addSession(['product_id' => 3524, 'stripe_token'=> 'MhvbcIOdL4AOeuywgSEi9Tsh']);

        $response = $this->get('/billing/failed');
        $response->assertResponseOk();
    }

    public function testPostBilling()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $data = [
              "first_name" => "Test",
              "last_name" => "Test",
              "company_name" => "Test Company",
              "phone" => "123456789",
              "address1" => "97 English Street",
              "address2" => "Test",
              "city" => "BUGLE RANGES",
              "country" => "South Australia",
              "post_code" => "5251",
              "state" => "South Australia",
              "Package" => "1",
              "stripeToken" => "1314678974479/8",
        ];

        $response = $this->post('/billing', $data);
        $response->assertResponseStatus(302);
    }

    public function testPostConfirmOrder()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $this->addSession(['product_id' => 3524, 'stripe_token'=> 'MhvbcIOdL4AOeuywgSEi9Tsh']);

        $response = $this->post('/billing/confirm');
        $response->assertResponseStatus(302);
    }
}
