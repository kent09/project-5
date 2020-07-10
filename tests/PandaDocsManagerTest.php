<?php

use App\User;
use App\InfsAccount;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PandaDocsManagerTest extends TestCase
{

    /**
     *
     * Basic Test for verifying the page
     *
     * @return Error
     */
    public function test_it_verifies_that_the_page_is_working()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/docs/pandadocs');
        
        $this->assertEquals(200, $response->status()); // Redirected
    }


    /**
     *
     * Basic test to get the template details
     *
     * @return Success
     */
    public function test_get_template_details()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'account_id' => $account_id->id,
            '_token' => 'tbeUgbNGfb2j6cGZocyc6f5vfHeVZm49GJhYUjGm',
            'tempID' => 'KnmVBWz8yNWGmGsEQWCse3',
        ];

        $response = $this->call('POST', '/docs/pandadocs/gettemplatedetails', $data);
        $this->assertEquals(200, $response->status());
    }


    /**
     *
     * Basic test to get the template details without middleware
     *
     * @return Redirect to Login Page
     */
    public function test_get_template_details_without_middleware()
    {
        $data = [
            'account_id' => InfsAccount::first()->id,
            '_token' => InfsAccount::first()->access_token,
            'tempID' => 'UYTKmvj4wsedRY9BSUhYcP',
        ];

        $response = $this->json('POST', 'docs/pandadocs/gettemplatedetails', $data);
        $response->assertResponseStatus(401); // Unauthorized or the user is not logged in
    }


    /**
     *
     * Basic test to create tags
     *
     * @return Success
     */
    public function test_create_tags()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'cat_name' => 'Demo Video Document Template',
            'temp_id' => 'KnmVBWz8yNWGmGsEQWCse3',
            'account_id' => $account_id,
            'type' => 'pandadoc',
        ];

        $response = $this->json('GET', 'docs/createtag', $data);
        $response->assertResponseOk();
    }


    /**
     *
     * Basic Test to get tags from this account
     *
     * @return Success
     */
    public function test_get_tags_from_this_account()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'IS_account_id' => $account_id->id,
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'tempID' => 'UYTKmvj4wsedRY9BSUhYcP',
            'template' => 'Support Docs Template',
        ];

        $response = $this->json('POST', 'docs/gettagsfromisaccount', $data);
        $response->assertResponseOk();
    }


    /**
     *
     * Basic Test to save tag selections
     *
     * @return Success
     */
    public function test_save_tag_selections()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $account_id = InfsAccount::where('user_id', $user->id)->first();

        $data = [
            'temp_name' => 'Support Docs Template',
            'temp_id' => 'UYTKmvj4wsedRY9BSUhYcP',
            'draft' => 469,
            'sent' => 471,
            'viewed' => 473,
            'completed' => 475,
            'voided' => 477,
            'rejected' => 479,
            'infsField' => [
                'document_sent_date' => [
                    'contact_field' => '_Dealer1TradingName',
                    'opportunity_field' => '_CCLJobID',
                ],
                'total_document_revenue' => [
                    'contact_field' => '_Dealer1BusinessName',
                    'opportunity_field' => 'ProjectedRevenueLow',
                ]
            ],
            'create_opp_if_not_exists' => 1,
            'stage_status' => [
                'draft' 	=> 37,
                'sent' 		=> 35,
                'viewed' 	=> 33,
                'completed' => 31,
                'voided' 	=> 29,
                'rejected' 	=> 27,
            ],
            'Posturl' => 'http://fusedtools.local/tools/panda',
            'clientfirstname' => 'SalesRep.FirstName',
            'contactfirstname' => '~Owner.FirstName~',
            'clientfirstname' => 'SalesRep.LastName',
            'contactfirstname' => '~Owner.LastName~',
            'clientfirstname' => 'SalesRep.Email',
            'contactfirstname' => '~Owner.Email~',
            'clientfirstname' => 'Client.FirstName',
            'contactfirstname' => '~Contact.FirstName~',
            'clientfirstname' => 'Client.LastName',
            'contactfirstname' => '~Contact.LastName~',
            'clientfirstname' => 'Client.Email',
            'contactfirstname' => '~Contact.Email~',
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'account_id' => $account_id->id,
            'type' => 'pandadoc',
        ];

        $response = $this->json('POST', 'docs/gettagsfromisaccount', $data);
        $response->assertResponseOk();
    }


    /**
     *
     * Basic test to save addtitional options
     *
     * @return Success
     */
    public function test_save_additional_options()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'saveDocFields' => 1,
            'infsField' => [
                'document_sent_date' => [
                    'contact_field' 	=> '_Dealer1TradingName',
                    'opportunity_field' => '_CCLJobID',
                ],
                'total_document_revenue' => [
                    'contact_field'		=> '_Dealer1BusinessName',
                    'opportunity_field' => 'ProjectedRevenueLow',
                ]
            ],
            'mostMostRecentOpportunity' => 1,
            'create_opp_if_not_exists' => 1,
            'stage_status' => [
                'draft' 	=> 37,
                'sent' 		=> 35,
                'viewed' 	=> 33,
                'completed' => 31,
                'voided' 	=> 29,
                'rejected' 	=> 27,
            ],
            '_token' => 'kLqsYLNxndfXaJTpk2rIwcSLe4GyfvBqH3110Mb4',
            'temp_id' => 'UYTKmvj4wsedRY9BSUhYcP',
        ];

        $response = $this->json('POST', 'docs/saveadditionaloptions', $data);
        $response->assertResponseOk();
    }


    /**
     *
     * Basic Test for HTTP Post Campaign
     *
     * @return Success
     */
    public function test_http_post_campaign()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $data = [
            'FuseKey' 			=> $user->FuseKey,
            'app' 				=> $user->infsAccounts()->first()->name,
            'TemplateID' 		=> 'UYTKmvj4wsedRY9BSUhYcP',
            'contactId' 		=> '~Contact.Id~',
            'doc_name' 			=> '~Company.Name~',
            'doc_message'	 	=> '',
            'Status' 			=> 1,
            'PricingTable' 		=> 1,
            'Client.FirstName' 	=> '',
            'Client.LastName' 	=> '',
            'Client.Email' 		=> '',
            'Client.Company' 	=> '',
            'Client.Phone' 		=> '',
            'SalesPerson.FirstName' => '',
            'SalesPerson.LastName' 	=> '',
            'SalesPerson.Email' 	=> '',
            'SalesPerson.Company' 	=> '',
            'SalesPerson.Phone' 	=> '',
            'IntroLetterToClient' 	=> '',
            'ClientFullName' 		=> '',
            'ClientJobTitle' 		=> '',
            'ClientPhone' 			=> '',
        ];

        $response = $this->json('POST', '/panda', $data);
        $response->assertResponseOk();
    }


    /**
     * A basic test for Getting Started/Guide.
     *
     * @return void
     */
    public function test_setup_guide()
    {
        $user = User::where('email', 'LIKE', '%ted@fusedsoftware.com%')->first();

        $this->signIn($user);

        $response = $this->call('GET', '/docs/pandadocs/setupguide');
        $this->assertEquals(200, $response->status());
    }
}
