<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CountryBasedOwnerTest extends TestCase
{
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/scripts/geo/countrybasedowner');

        $this->assertEquals(200, $response->status());
    }

    public function test_it_verifies_that_it_retrieves_the_country_owner_groups()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'accountID' => 36,
            '_token' => 'RA7njKUa8q5ERLU2fEmqB0YdlVt1Jjr5zyAUDodB',
        ];


        $this->json('POST', '/api/v1/country-owner-groups/get/by-user-infusionsoft-account-id', $data)
             ->seeJsonStructure([
                 'success' => [
                     'data' => [
                          '*' => [
                             'id', 'country_owner_groups', 'infs_account_id', 'infs_person_id', 'owner_name'
                         ]
                     ],
                     'message',
                     'status_code'
                 ]
            ]);
    }

    public function test_it_verifies_that_it_can_edit_owners_group()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'accountID' => 36
        ];

        $this->json('GET', '/api/v1/country-owners', $data)
             ->seeJsonStructure([
                 'success' => [
                     'data' => [
                         'countries', 'owners'
                     ],
                     'message',
                     'status_code'
                 ]
            ]);
    }


    public function test_it_verifies_that_it_can_save_the_edited_owners_group()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            "owner_id" => 8834,
            "country_id" => [2,4,5,6,7,8,9],
            "infs_account_id" => 36,
            "owner_name" => [
                "Email" => "ted@fusedsoftware.com",
                "FirstName"=>"Teddy",
                "LastName"=>"Patriarca",
                "Id" => 8834
            ],
            "id" => 11
        ];

        $this->json('PATCH', '/api/v1/country-based-owner/11', $data)
             ->seeJsonStructure([
                 'success'
            ]);
    }


    public function test_it_verifies_that_it_can_delete_the_owner_group()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $this->json('DELETE', '/api/v1/country-owners/10', [])
             ->seeJsonStructure([
                 'success'
            ]);
    }

    public function test_it_verifies_that_it_can_add_new_owner_group()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            "owner_id" => 8834,
            "country_id" => [4,5,6],
            "infs_account_id" => 36,
            "owner_name" => [
                "Email" => "ted@fusedsoftware.com",
                "FirstName" => "Teddy",
                "LastName" => "Patriarca",
                "Id" => 8834
            ]
        ];

        $this->json('POST', '/api/v1/country-based-owner', $data)
             ->seeJsonStructure([
                 'success'
            ]);
    }

    public function test_it_verifies_that_it_can_add_new_fallback_owner()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            "fallback_owner_id" => 1,
            "infs_account_id" => 36,
            "owner_name" => [[
                "Email" 	=> "james@outoftheboxsolutions.com.au",
                "FirstName" => "James",
                "LastName" 	=> "Jackson",
                "Id" 		=> 1
            ]]
        ];

        $this->json('POST', '/api/v1/fallback-owner', $data)
             ->seeJsonStructure([
                 'success'
            ]);
    }


    public function test_it_verifies_that_it_can_update_fallback_owner()
    {
        $user = \App\User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            "fallback_owner_id" => 8552,
            "infs_account_id" => 36,
            "owner_name" => [[
                "Email" 	=> "loremuelgadrinab@gmail.com",
                "FirstName" => "Loremuel",
                "LastName" 	=> "Gadrinab",
                "Id" 		=> 8552
            ]]
        ];

        $this->json('PATCH', '/api/v1/fallback-owner/36', $data)
             ->seeJsonStructure([
                 'success'
            ]);
    }

    public function test_it_verifies_that_it_can_run_the_scripts_fallback_owner()
    {
        $data = [
            'FuseKey' => 'c81e728d9d',
            'mode' => 'country_owner',
            'infs_app_name' => 'zl328',
            'contactId' => 7775,
            'Country' => 'Albania',
            'include_closed' => 0,
            'skip_opps' => 0,
        ];

        $this->json('POST', '/scripts', $data)
             ->seeJsonStructure([]);
    }
}
