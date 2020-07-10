<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CopyValueFieldsTest extends TestCase
{
    public function testCopyValueField()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();

        $data = [
            'mode' => 'copy_values',
            'FuseKey' => $user->FuseKey,
            'app' => $user->infsAccounts()->first()->name,
            'contactID' => '8772',
            'method' => 'replace',
            'fieldfrom' => 'Contact._CustomField1',
            'fieldto' => 'Contact._CustomField2'
        ];

        $response = $this->json('POST', 'scripts', $data);
        $response->assertResponseStatus(500); // redirection back to the sync dashboard page
    }
}
