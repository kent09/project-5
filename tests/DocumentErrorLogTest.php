<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DocumentErrorLogTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_it_to_verifies_the_page_if_working()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->call('GET', '/docs/notifications');
        $this->assertEquals(200, $response->status());
    }

    public function test_it_to_get_the_list()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $response = $this->json('GET', '/docs/notifications');
        $response->assertResponseStatus(200);
    }
}
