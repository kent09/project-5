<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderProductFieldTest extends TestCase
{
    public function testOrderProductFieldTest()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();

        $data = [
            'mode' => 'store_product_names',
            'FuseKey' => $user->FuseKey,
            'app' => $user->infsAccounts()->first()->name,
            'contactID' => '8772',
            'method' => 'append',
            'fieldto' => 'Contact._ProductNames'
        ];

        $response = $this->json('POST', 'scripts', $data);
        $response->assertResponseStatus(500); // redirection back to the sync dashboard page
    }
}
