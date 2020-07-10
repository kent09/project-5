<?php

use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocumentHistoryTest extends TestCase
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

        $response = $this->call('GET', 'docs/history');

        $this->assertEquals(200, $response->status());
    }

    /**
     *
     * Basic Test to get the Document History
     *
     */
    public function test_to_get_document_history()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->json('GET', 'docs/history');

        $response->assertResponseOk();
    }
}
