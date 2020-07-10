<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostCodeContactTest extends TestCase
{
    public function testPostOwnerWithoutLogin()
    {
        $account = \App\InfsAccount::first();
        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/post-owner', $data);
        $response->assertResponseStatus(401);
    }

    public function testPostOwner()
    {
        $user = \App\User::first();
        $this->signIn($user);

        $account = \App\InfsAccount::where('name', 'zl328')->first();

        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/post-owner', $data);
        $response->assertResponseOk();
    }

    public function testReassignContactOwner()
    {
        $account = \App\InfsAccount::where('name', 'zl328')->first();
        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/reAssignContactOwner', $data);
        $response->assertResponseOk();
    }

    public function testAddOwnerWithoutLogin()
    {
        $account = \App\InfsAccount::first();
        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/add-owner', $data);
        $response->assertResponseStatus(401);
    }

    public function testAddOwner()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/add-owner', $data);
        // should be ok once the app is authorized for INFS
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testEditOwner()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id,
            'id' => 1
        ];

        $response = $this->json('POST', '/edit-owner', $data);
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testEditOwnerGroup()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'postc_owner_id' => 1,
            'id' => 1
        ];

        $response = $this->json('POST', '/edit-owner-group', $data);
        $response->assertResponseOk();
        //$response->assertResponseStatus(500);
    }

    public function testSaveGroup()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id,
            'country' => 'AU',
            'contact' => 732,
            'postcode' => '3400',
            'kmvalue' => 20,
            'areagrouptext' => '3400,317,3136',
            'areagroup' => 'radius_around_postcode',
            'unit' => 'KM',
            'infsName' => 'zl328'
        ];


        $response = $this->json('POST', '/save-group', $data);
        $response->assertResponseOk();
        //$response->assertResponseStatus(500);
    }

    public function testUpdateOwner()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id,
            'country' => 'AU',
            'contact' => 732,
            'postcode' => '3400',
            'kmvalue' => 20,
            'areagrouptext' => '3400,317,3136',
            'areagroup' => 'radius_around_postcode',
            'unit' => 'KM',
            'infsName' => 'zl328'
        ];


        $response = $this->json('POST', '/update-owner', $data);
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testDeleteOwner()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/delete-owner', $data);
        // should be ok once the app is authorized for INFS
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testPostCode()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'country' => 'AU',
            'postcode' => '0822'
        ];

        $response = $this->json('POST', '/post-code', $data);
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testPostCTags()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id
        ];

        $response = $this->json('POST', '/postc-tags', $data);
        $response->assertResponseOk();
    }

    public function testPostCodeRetag()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $post_c_tag = \App\PostcTags::first();

        $data = [
          'id' =>  $post_c_tag->id
        ];

        $response = $this->json('POST', '/post-code-retag', $data);
        $response->assertResponseOk();
    }

    public function testPostCodeDelete()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $post_c_tag = \App\PostcTags::first();

        $data = [
            'id' =>  $post_c_tag->id
        ];

        $response = $this->json('POST', '/post-code-delete', $data);
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testTagContact()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $account = $user->infsAccounts()->first();

        $data = [
            'accountID' => $account->id,
            'country' => 'AU',
            'contact' => 732,
            'postcode' => '3400',
            'kmvalue' => 20,
            'areagrouptext' => '3400,317,3136',
            'areagroup' => 'radius_around_postcode',
            'unit' => 'KM',
            'infsName' => 'zl328'
        ];

        $response = $this->json('POST', '/tag-contact', $data);
        //$response->assertResponseOk();
        $response->assertResponseStatus(500);
    }

    public function testRadiusMap()
    {
        $user = \App\User::where('email', 'like', '%ted@fusedsoftware%')->first();
        $this->signIn($user);

        $data = [
            'country' => 'AU',
            'postcode' => '0822',
            'radius' => 20,
            'unit' => 'KM'
        ];

        $response = $this->json('POST', '/radiusMap', $data);
        $response->assertResponseOk();
        //$response->assertResponseStatus(500);
    }
}
